<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 02/09/2017
 * Time: 18.33
 */

namespace App\Controller;

use App\Controller\Interfaces\ModalEventController;
use App\Entity\Traits\CloneableTrait;
use App\Entity\Traits\FieldsTrait;
use App\Form\Data\ImportDataType;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ImportDataController extends Controller implements ModalEventController
{
    /**
     * Controller for importing data, first step: chosing the entity and supplying the csv data.
     *
     * @Route("/{_locale}/admin/import", name="nav.admin_import")

     * @param Request $request
     * @return mixed
     */
    public function importDataAction(Request $request)
    {
        $session = $request->getSession();
        //$user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        //$tables = $em->getConnection()->getSchemaManager()->listTables();

        $entities = [];
        $meta = $em->getMetadataFactory()->getAllMetadata();
        /** @var ClassMetadata $m */
        foreach ($meta as $m) {
            $entities[] = $m->getName();
        }
        sort($entities);

        $data = $session->get('import_data');
        if (!$data)
            $data = [];

        $form = $this->createForm(ImportDataType::class, $data,
            ['validation_groups' => ['none'], 'entities' => $entities, 'csv' => null, 'delim' => "\t"]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityName = $form->getData()['entity'];
            /** @var FieldsTrait $entity */
            $entity = new $entityName;
            $csv = explode("\n", $form->getData()['csv']);
            $delim = ImportDataType::DELIM_VALUES[$form->getData()['delim']];
            $fields = str_getcsv(trim($csv[0]), $delim, "'");

            if (count($fields) > 0) {
                $session->set('import_data', [
                    'entity' => $entityName,
                    'csv' => $form->getData()['csv'],
                    'delim' => $form->getData()['delim'],
                    'vars' => $entity->getFields(),
                    'vals' => $fields,
                    'step' => 2,
                    'mapping' => array_key_exists('mapping', $data) ? $data['mapping'] : [],
                ]);
                return $this->redirectToRoute('nav.admin_map_import');
            }
        } else if ($form->isSubmitted()) {
            print_r($form->getErrors()->current()->getMessage());
        }
        return $this->render('admin/import/start.html.twig', [
            'form' => $form ? $form->createView() : null,
        ]);
    }

    /**
     * Controller for importing data, second step: mapping the entities fields to the supplied csv data.
     *
     * @Route("/{_locale}/admin/map_import", name="nav.admin_map_import")

     * @param Request $request
     * @return mixed
     */
    public function mapImportDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $data = $session->get('import_data');
        if (!$data)
            $data = [];
        else
            $data = array_key_exists('mapping', $data) ?
                array_intersect_key($data['mapping'], $session->get('import_data')['vars']) :
                [];

        $entity = $session->get('import_data')['entity'];
        /** @var ClassMetadataInfo $metadata */
        $metadata = $em->getClassMetaData($entity);
        $assocs = ['id' => 0];
        foreach (array_keys($session->get('import_data')['vars']) as $field) {
            if ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field)) {
                $assocs[$field] = 0;
            }
        }
        $session->set('to_one_assocs', $assocs);

        $form = $this->createForm(ImportDataType::class, $data,
            [
                'step' => 2,
                'vars' => $session->get('import_data')['vars'],
                'vals' => $session->get('import_data')['vals'],
                'offsets' => $session->get('to_one_assocs'),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $import_data = $session->get('import_data');
            $import_data['mapping'] = $form->getData();
            $session->set('import_data', $import_data);
            return $this->redirectToRoute('nav.admin_finalize_import');
        }
        return $this->render('admin/import/map.html.twig', [
            'form' => $form ? $form->createView() : null,
        ]);
    }

    /**
     * Controller for importing data, third step: finalizing the import.
     *
     * @Route("/{_locale}/admin/finalize_import", name="nav.admin_finalize_import")

     * @param Request $request
     * @return mixed
     */
    public function finalizeImportDataAction(Request $request)
    {
        ini_set('max_execution_time', 0);

        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $entity = $session->get('import_data')['entity'];
        $delim = ImportDataType::DELIM_VALUES[$session->get('import_data')['delim']];


        $temp_file = tempnam(sys_get_temp_dir(), 'csv-data');
        file_put_contents($temp_file, $session->get('import_data')['csv']);
        $fhandle = fopen($temp_file, 'r');
        $keys = fgetcsv($fhandle, 0, $delim, "'");

        $mapping = $session->get('import_data')['mapping'];
        $offsets = [];
        array_walk($mapping, function(&$item, $key) use ($keys, &$offsets) {
            if (!is_numeric($item)) {
                $item = array_search($item, $keys);
            } else if (strpos($key, '_import_offset') !== false) {
                // $key = substr($key, 0, strlen($key) - strlen('_import_offset'));
                $offsets[$key] = $item;
                // $item = -5;
            } else {
                $item -= 4;
            }
        });

        $form = $this->createForm(ImportDataType::class, null,
            [
                'step' => 3,
            ]
        );
        $imported = 0;
        $object = new $entity;
        $parsed = [];

        while ($csv_data = fgetcsv($fhandle, 0, $delim, "'")) {
            $data = array_merge($mapping);
            $data = array_diff_key($data, $offsets);
            /** @var CloneableTrait $object */
            array_walk($data, function (&$item, &$key, $vals) use ($em, $object, $offsets) {
                if ($item !== null) {
                    switch ($item) {
                        case -6:
                            $item = null;
                            break;
                        case -5:
                            $item = null;
                            break;
                        case -4:
                            $item = false;
                            break;
                        case -3:
                            $item = true;
                            break;
                        case -2:
                            $item = new \DateTime('now');
                            break;
                        case -1:
                            $item = $this->get('security.token_storage')->getToken()->getUser();
                            break;
                        default:
                            if (array_key_exists($item, $vals) && $vals[$item] == "NULL") {
                                $item = null;
                            } else {
                                /** @var ClassMetadataInfo $metadata */
                                $metadata = $em->getClassMetaData(get_class($object));
                                if ($metadata->hasAssociation($key)) {
                                    $mapping = $metadata->getAssociationMapping($key);
                                    $entityId = $vals[$item];
                                    if (array_key_exists($key . '_import_offset', $offsets))
                                        $entityId += $offsets[$key . '_import_offset'];
                                    $entity = $em->getRepository($mapping['targetEntity'])->find($entityId);

                                    if ($entity)
                                        $item = $entity;
                                    else
                                        $item = null;
                                } else {
                                    $item = $vals[$item];
                                    if (array_key_exists($key . '_import_offset', $offsets))
                                        $item += $offsets[$key . '_import_offset'];
                                }
                            }
                            break;
                    }
                }
            }, $csv_data);
            $parsed[$imported++] = $data;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imported = 0;
            foreach ($parsed as $data) {
                /** @var CloneableTrait $object */
                $object = new $entity;
                $object->fill($data, false);
                $class = get_class($object);
                /** @var ClassMetadataInfo $metadata */
                $metadata = $em->getClassMetaData($class);

                if ($data['id']) {
                    while ($class) {
                        $metadata->setIdGenerator(new AssignedGenerator());
                        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
                        $class = get_parent_class($class);
                    }
                }
                $em->persist($object);
                if ($data['id']) {
                    $class = get_class($object);
                    while ($class) {
                        $metadata = $em->getClassMetaData($class);
                        $metadata->setIdGenerator(new IdentityGenerator());
                        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);
                        $class = get_parent_class($class);
                    }
                }
                $imported++;
            }
            $em->flush();

            $trans = $this->get('translator');
            $this->addFlash('success',
                $trans->transChoice('imported_info', $imported, ['%count%' => $imported], 'import'));

            $import_data = $session->get('import_data');
            $import_data['csv'] = null;
            $session->set('import_data', $import_data);

            return $this->redirectToRoute('nav.admin_import');
        }
        return $this->render('admin/import/finalize.html.twig', [
            'form' => $form ? $form->createView() : null,
            'mapping' => array_diff_key($mapping, $offsets),
            'keys' => $keys,
            'json_objects' => json_encode($parsed),
        ]);
    }
}