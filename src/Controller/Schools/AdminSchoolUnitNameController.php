<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 31/12/2017
 * Time: 21.49
 */

namespace App\Controller\Schools;

use App\Controller\Interfaces\ModalEventController;
use App\Controller\Traits\RESTFulControllerTrait;
use App\Controller\Traits\TableSortingControllerTrait;
use App\Entity\Schools\SchoolUnit;
use App\Entity\Schools\SchoolUnitName;
use App\Form\School\EditUnitNameType as SchoolUnitNameEditType;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use App\Entity\Api\Message as ApiMessage;

/**
 * Class AdminSchoolUnitNameController
 * - Handles SchoolUnitName entities connected to a SchoolUnit entity.
 * - API calls for admin.
 * - Admin funtionality for UI.
 *
 * @package App\Controller\Schools
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class AdminSchoolUnitNameController extends FOSRestController implements ModalEventController
{
    /** Use some REST methods */
    use RESTFulControllerTrait;

    /** Use table sorting */
    use TableSortingControllerTrait;

    /**
     * Controller for listing school unit names (admin only).
     *
     * @Route("/api/v2/admin/schools/units/{schoolUnit}/names/list",
     *     name="api.admin_list_school_unit_names",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/schools/units/{schoolUnit}/names/list",
     *     options={"expose"=true},
     *     name="nav.admin_list_school_unit_names")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns a list of school unit names",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Nelmio\Model(type=SchoolUnitName::class, groups={"SchoolApi","Default"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="schoolUnit",
     *     in="path",
     *     type="integer",
     *     description="The id of the owning school unit"
     * )
     *
     * @param integer $schoolUnit
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminListSchoolUnitNamesAction($schoolUnit = 0, $id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SchoolAPI', 'Default']);

        /** @var SchoolUnit $schoolUnit */
        $schoolUnit = $em->getRepository(SchoolUnit::class)->find($schoolUnit);
        if (!$id && !$schoolUnit)
            return $this->notFoundError('schoolUnit', $schoolUnit, Response::HTTP_NOT_FOUND);

        // toggle active / inactive state
        if ($id) {
            /** @var SchoolUnitName $name */
            $name = $em->getRepository(SchoolUnitName::class)->find($id);

            if (!$name)
                return $this->notFoundError('id', $id);

            $name->setIsActive(!$name->getIsActive());
            $em->merge($name);
            $em->flush();

            if ($this->isRestfulRequest($request))
                return $this->readEntity(SchoolUnitName::class, $id, ['SchoolAPI', 'Default']);

            $schoolUnit = $name->getSchoolUnit();
        }

        // handle sorting
        list($sort, $sortKey,$order, $orders) = $this->handleSorting(
            $request,
            ['name', 'from', 'until', 'isActive'],
            'admin_school_unit_names',
            'name'
        );

        // fetch
        $er = $em->getRepository(SchoolUnitName::class);
        $names = $er->findBy(['schoolUnit' => $schoolUnit], $sort);

        // close for modal
        $fb = $this->createFormBuilder([], [
            'translation_domain' => 'school',
            'attr' => ['action' => $request->getPathInfo()]
        ]);
        $fb
            ->add('close', ButtonType::class, [
                'translation_domain' => 'messages',
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                ],
                'label' => 'action.close',
            ]);

        $form = $fb->getForm();

        // Return a RESTful JSON response or HTML
        $view = $this->view($names, Response::HTTP_OK)
            ->setTemplate('admin/schools/units/names/list.html.twig')
            ->setTemplateVar('schoolUnitNames')
            ->setTemplateData([
                'schoolUnit' => $schoolUnit,
                'orders' => $orders,
                'order' => $order,
                'sort' => $sortKey,
                'modal' => $request->isXmlHttpRequest(),
                'btns' => [$form->createView()->offsetGet('close')],
            ])
            ->setContext($ctx)
        ;
        return $this->handleView($view);
    }

    /**
     * Controller for toggling active / inactive state of one school unit name (admin only).
     *
     * @Route("/api/v2/admin/schools/units/names/{id}/toggle",
     *     name="api.admin_toggle_school_unit_name",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/schools/units/names/{id}/toggle",
     *     options={"expose"=true},
     *     name="nav.admin_toggle_school_unit_name")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit name",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolUnitName::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit name was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminToggleSchoolUnitNameAction($id = 0, Request $request)
    {
        return $this->adminListSchoolUnitNamesAction(0, $id, $request);
    }

    /**
     * Controller for reading a single school unit name (admin only).
     * RESTful API only (GET).
     *
     * @Route("/api/v2/admin/schools/units/names/{id}/read",
     *     name="api.admin_read_school_unit_name",
     *     methods={"GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit name",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolUnitName::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit name was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the school unit name"
     * )
     *
     * @param integer $id
     * @return mixed
     */
    public function adminReadSchoolUnitNameRESTAction($id)
    {
        return $this->readEntity(SchoolUnitName::class, $id, ['SchoolAPI', 'Default']);
    }

    /**
     * Controller for editing a single school unit name (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/schools/units/names/{id}/edit",
     *     name="api.admin_edit_school_unit_name",
     *     methods={"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit name",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolUnitName::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit name was found with the id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=409,
     *     description="The query paramters were not valid",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the school unit name"
     * )
     *
     * @param integer $id
     * @param integer $schoolUnit
     * @param Request $request
     * @return mixed
     */
    public function adminEditSchoolUnitNameRESTAction($id = 0, $schoolUnit = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SchoolAPI', 'Default']);

        // Fetch or create the entity
        if ($id) {
            $name = $em->getRepository(SchoolUnitName::class)->find($id);
            if (!$name) {
                return $this->notFoundError('id', $id);
            }
        } else {
            $name = new SchoolUnitName();
            /** @var SchoolUnit $schoolUnit */
            $schoolUnit = $em->getRepository(SchoolUnit::class)->find($schoolUnit);
            if (!$schoolUnit)
                return $this->notFoundError('schoolUnit', $schoolUnit, Response::HTTP_NOT_FOUND);
            $name->setSchoolUnit($schoolUnit);
            $name->fill($schoolUnit->getName()->getFields(['id', 'schoolUnit']));
            $name->setFrom(new \DateTime())->setUntil(null)->setIsActive(true);
        }

        $form = $this->createForm(SchoolUnitNameEditType::class, $name, [
            'csrf_protection' => false,
            'is_api' => true,
        ]);

        $form->submit($request->request->all());

        if ($form->isValid() && $form->isSubmitted()) {
            if ($name->getId())
                $em->merge($name);
            else
                $em->persist($name);
            $em->flush();

            return $this->readEntity(SchoolUnitName::class, $name->getId(), $ctx->getGroups());
        } else if (!$form->isSubmitted()) {
            return $this->notValidError();
        }

        $err = $form->getErrors(true, true);
        $errorsList = [];
        foreach ($err as $it) {
            $errorsList[(string)$it->getOrigin()->getPropertyPath()] = $it->getMessage();
        }
        return $this->errors($errorsList);
    }

    /**
     * Controller for creating a single school unit name (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/schools/units/{schoolUnit}/names/create",
     *     name="api.admin_create_school_unit_name",
     *     methods={"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit name",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolUnitName::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit  was found with the schoolUnit parameter",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=409,
     *     description="The query paramters were not valid",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="schoolUnit",
     *     in="path",
     *     type="integer",
     *     description="The id of the owning school unit"
     * )
     *
     * @param integer $schoolUnit
     * @param Request $request
     * @return mixed
     */
    public function adminCreateSchoolUnitNameRESTAction($schoolUnit = 0, Request $request)
    {
        return $this->adminEditSchoolUnitNameRESTAction(0, $schoolUnit, $request);
    }

    /**
     * Controller for creating or editing a single school unit name (admin only).
     * RESTful API separate (adminCreateOrEditSchoolUnitNameRESTAction).
     *
     * @Route("/{_locale}/admin/schools/units/{schoolUnit}/names/create",
     *     options={"expose"=true},
     *     name="nav.admin_create_school_unit_name")
     * @Route("/{_locale}/admin/schools/units/names/{id}/edit",
     *     options={"expose"=true},
     *     name="nav.admin_edit_school_unit_name")
     * @param integer $id
     * @param integer $schoolUnit
     * @param Request $request
     * @return mixed
     */
    public function adminCreateOrEditSchoolUnitNameAction($id = 0, $schoolUnit = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id) {
            $schoolUnitName = $em->getRepository(SchoolUnitName::class)->find($id);
        } else {
            $schoolUnitName = new SchoolUnitName();
            $schoolUnit = $em->getRepository(SchoolUnit::class)->find($schoolUnit);
            $schoolUnitName->setSchoolUnit($schoolUnit);
            $schoolUnitName->fill($schoolUnit->getName()->getFields(['id', 'schoolUnit']));
            $schoolUnitName->setFrom(new \DateTime())->setUntil(null)->setIsActive(true);
        }

        $form = $this->createForm(SchoolUnitNameEditType::class, $schoolUnitName, [
            'attr' => ['action' => $request->getPathInfo()],
            'delete_title' => $this->get('translator')->trans('label.delete', [], 'school'),
            'delete_path' => $this->generateUrl('nav.admin_delete_school_unit_name', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $action = $schoolUnitName->getId() ? 'updated' : 'saved';

            if ($schoolUnitName->getId()) {
                $em->merge($schoolUnitName);
                $em->flush();
            } else {
                $em->persist($schoolUnitName);
                $em->flush();
                $schoolUnit = $schoolUnitName->getSchoolUnit();
                $old = $schoolUnit->getName();
                if ($schoolUnitName->getFrom() > $old->getFrom()) {
                    $old->setIsActive(false)->setUntil($schoolUnitName->getFrom());
                    $em->persist($old);
                    $em->flush();
                }
            }

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.school_unit_name.' . $action,
                    'parameters' => ['%name%' => $schoolUnitName->getName()]
                ]);
            // return an empty response for the ajax modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_list_school_unit_names');

        } else if ($form->isSubmitted() && !$form->isValid()) {
            // return error code for modal and ok for non-modals
            $formView = $form->createView();
            return $this->render('admin/schools/units/names/form.html.twig', [
                'schoolUnitName' => $schoolUnitName,
                'form' => $formView,
                'modal' => $request->isXmlHttpRequest(),
                'btns' => $id ?
                    [
                        $formView->offsetGet('close'),
                        $formView->offsetGet('delete'),
                        $formView->offsetGet('submit')
                    ] :
                    [
                        $formView->offsetGet('close'),
                        $formView->offsetGet('submit')
                    ]
            ], new Response('', $request->isXmlHttpRequest() ?
                Response::HTTP_BAD_REQUEST :
                Response::HTTP_OK));
        }

        $formView = $form->createView();
        return $this->render('admin/schools/units/names/form.html.twig', [
            'schoolUnitName' => $schoolUnitName,
            'form' => $formView,
            'modal' => $request->isXmlHttpRequest(),
            'btns' => $id ?
                [
                    $formView->offsetGet('close'),
                    $formView->offsetGet('delete'),
                    $formView->offsetGet('submit')
                ] :
                [
                    $formView->offsetGet('close'),
                    $formView->offsetGet('submit')
                ]
        ]);
    }

    /**
     * Controller for deleting a single school unit name (admin only).
     *
     * @Route("/api/v2/admin/schools/units/names/{id}/delete",
     *     name="api.admin_delete_school_unit_name",
     *     methods={"DELETE"})
     * @Route("/{_locale}/admin/schools/units/names/{id}/delete",
     *     options={"expose" = true},
     *     name="nav.admin_delete_school_unit_name")
     *
     * @SWG\Response(
     *     response=200,
     *     description="School unit name was deleted",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit name was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the school unit name"
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminDeleteSchoolUnitNameAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var SchoolUnitName $schoolUnitName */
        $schoolUnitName = $em->getRepository(SchoolUnitName::class)->find($id);

        // handle rest calls
        if ($schoolUnitName && $this->isRestfulRequest($request)) {
            $em->remove($schoolUnitName);
            $em->flush();

            return $this->ok();

        } else if ($this->isRestfulRequest($request)) {
            return $this->notFoundError('id', $id);
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => 'school',
            'attr' => ['action' => $request->getPathInfo()]
        ]);
        $fb
            ->add('yes', SubmitType::class, ['left_icon' => 'fa-trash', 'right_icon' => 'fa-check', 'attr' => ['class' => 'btn-danger'], 'label' => 'action.yes']);

        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $name = $schoolUnitName->getName();

            if ($schoolUnitName->getIsActive()) {
                /** @var SchoolUnitName $pred */
                $pred = $schoolUnitName->getPredecessors()->last();
                $pred->setUntil(null)->setIsActive(true);
                $em->persist($pred);
            }
            $em->remove($schoolUnitName);
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.school_unit_name.deleted',
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse(['reloadPage' => 1], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_list_school_unit_names');
        }

        return $this->render('admin/schools/units/names/delete.html.twig', [
            'schoolUnitName' => $schoolUnitName,
            'form' => $form->createView(),
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }
}