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
use App\Entity\Invoicing\InvoiceAddress;
use App\Entity\Schools\SchoolUnit;
use App\Form\Invoicing\InvoiceAddressEditType as InvoiceAddressEditType;
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
 * Class AdminSchoolUnitAddressController
 * - Handles InvoicingAddress entities connected to a SchoolUnit entity.
 * - API calls for admin.
 * - Admin funtionality for UI.
 *
 * @package App\Controller\Schools
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class AdminSchoolUnitAddressController extends FOSRestController implements ModalEventController
{
    /** Use some REST methods */
    use RESTFulControllerTrait;

    /** Use table sorting */
    use TableSortingControllerTrait;

    /**
     * Controller for listing school unit addresses (admin only).
     *
     * @Route("/api/v2/admin/schools/units/{schoolUnit}/addresses/list",
     *     name="api.admin_list_school_unit_addresses",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/schools/units/{schoolUnit}/addresses/list",
     *     options={"expose"=true},
     *     name="nav.admin_list_school_unit_addresses")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns a list of school unit addresses",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Nelmio\Model(type=InvoiceAddress::class, groups={"SchoolApi","Default"}))
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
    public function adminListSchoolUnitAddressesAction($schoolUnit = 0, $id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SchoolAPI', 'Default']);

        /** @var SchoolUnit $schoolUnit */
        $schoolUnit = $em->getRepository(SchoolUnit::class)->find($schoolUnit);
        if (!$id && !$schoolUnit)
            return $this->notFoundError('schoolUnit', $schoolUnit, Response::HTTP_NOT_FOUND);

        // Toggle active / inactive state
        if ($id) {
            /** @var InvoiceAddress $name */
            $address = $em->getRepository(InvoiceAddress::class)->find($id);

            if (!$address)
                return $this->notFoundError('id', $id);

            $address->setIsActive(!$address->getIsActive());
            $em->merge($address);
            $em->flush();

            if ($this->isRestfulRequest($request))
                return $this->readEntity(InvoiceAddress::class, $id, ['SchoolAPI', 'Default']);

            $schoolUnit = $address->getSchoolUnit();
        }

        // handle sorting
        list($sort, $sortKey,$order, $orders) = $this->handleSorting(
            $request,
            ['name', 'streetAddress', 'zipcode', 'isActive'],
            'admin_school_unit_addresses',
            'name'
        );

        // fetch
        $er = $em->getRepository(InvoiceAddress::class);
        $addresses = $er->findBy(['schoolUnit' => $schoolUnit], $sort);

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
        $view = $this->view($addresses, Response::HTTP_OK)
            ->setTemplate('admin/schools/units/addresses/list.html.twig')
            ->setTemplateVar('addresses')
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
     * Controller for toggling active / inactive state of one school unit addresses (admin only).
     *
     * @Route("/api/v2/admin/schools/units/addresses/{id}/toggle",
     *     name="api.admin_toggle_school_unit_address",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/schools/units/addresses/{id}/toggle",
     *     options={"expose"=true},
     *     name="nav.admin_toggle_school_unit_address")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit address",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=InvoiceAddress::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit address was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the address to toggle (active / inactive)"
     * )
     ** @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminToggleSchoolUnitAddressAction($id = 0, Request $request)
    {
        return $this->adminListSchoolUnitAddressesAction(0, $id, $request);
    }

    /**
     * Controller for reading a single school unit address (admin only).
     * RESTful API only (GET).
     *
     * @Route("/api/v2/admin/schools/units/addresses/{id}/read",
     *     name="api.admin_read_school_unit_address",
     *     methods={"GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit address",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=InvoiceAddress::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit address was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the school unit address"
     * )
     *
     * @param integer $id
     * @return mixed
     */
    public function adminReadSchoolUnitAddressRESTAction($id)
    {
        return $this->readEntity(InvoiceAddress::class, $id, ['SchoolAPI', 'Default']);
    }

    /**
     * Controller for editing a single school unit address (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/schools/units/addresses/{id}/edit",
     *     name="api.admin_edit_school_unit_address",
     *     methods={"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit address",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=InvoiceAddress::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit address was found with the id",
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
     *     description="The id of the school unit address"
     * )
     *
     * @param integer $id
     * @param integer $schoolUnit
     * @param Request $request
     * @return mixed
     */
    public function adminEditSchoolUnitAddressRESTAction($id = 0, $schoolUnit = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SchoolAPI', 'Default']);

        // Fetch or create the entity
        if ($id) {
            $address = $em->getRepository(InvoiceAddress::class)->find($id);
            if (!$address) {
                return $this->notFoundError('id', $id);
            }
        } else {
            $address = new InvoiceAddress();
            /** @var SchoolUnit $schoolUnit */
            $schoolUnit = $em->getRepository(SchoolUnit::class)->find($schoolUnit);
            if (!$schoolUnit)
                return $this->notFoundError('schoolUnit', $schoolUnit, Response::HTTP_NOT_FOUND);
            $address->setSchoolUnit($schoolUnit);
            $address->fill($schoolUnit->getName()->getFields(['id', 'schoolUnit']));
        }

        $form = $this->createForm(InvoiceAddressEditType::class, $address, [
            'csrf_protection' => false,
            'is_api' => true,
        ]);

        $form->submit($request->request->all());

        if ($form->isValid() && $form->isSubmitted()) {
            if ($address->getId())
                $em->merge($address);
            else
                $em->persist($address);
            $em->flush();

            return $this->readEntity(InvoiceAddress::class, $address->getId(), $ctx->getGroups());
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
     * Controller for creating a single school unit address (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/schools/units/{schoolUnit}/addresses/create",
     *     name="api.admin_create_school_unit_address",
     *     methods={"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit address",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=InvoiceAddress::class, groups={"SchoolApi","Default"})
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
    public function adminCreateSchoolUnitAddressRESTAction($schoolUnit = 0, Request $request)
    {
        return $this->adminEditSchoolUnitAddressRESTAction(0, $schoolUnit, $request);
    }

    /**
     * Controller for creating or editing a single school unit adddress (admin only).
     * RESTful API separate (adminCreateOrEditSchoolUnitAddressRESTAction).
     *
     * @Route("/{_locale}/admin/schools/units/{schoolUnit}/addresses/create",
     *     options={"expose"=true},
     *     name="nav.admin_create_school_unit_address")
     * @Route("/{_locale}/admin/schools/units/addresses/{id}/edit",
     *     options={"expose"=true},
     *     name="nav.admin_edit_school_unit_address")
     * @param integer $id
     * @param integer $schoolUnit
     * @param Request $request
     * @return mixed
     */
    public function adminCreateOrEditSchoolUnitAddressAction($id = 0, $schoolUnit = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id) {
            $address = $em->getRepository(InvoiceAddress::class)->find($id);
        } else {
            $address = new InvoiceAddress();
            /** @var SchoolUnit $schoolUnit */
            $schoolUnit = $em->getRepository(SchoolUnit::class)->find($schoolUnit);
            $address->setSchoolUnit($schoolUnit);
            $address->fill($schoolUnit->getName()->getFields(['id', 'schoolUnit']));
        }

        $form = $this->createForm(InvoiceAddressEditType::class, $address, [
            'attr' => ['action' => $request->getPathInfo()],
            'delete_title' => $this->get('translator')->trans('label.delete', [], 'school'),
            'delete_path' => $this->generateUrl('nav.admin_delete_school_unit_address', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $action = $address->getId() ? 'updated' : 'saved';

            if ($address->getId()) {
                $em->merge($address);
            } else {
                $em->persist($address);
            }
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.school_unit_address.' . $action,
                    'parameters' => ['%name%' => $address->getName()]
                ]);

            // return an empty response for the ajax modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_list_school_unit_addresses');

        } else if ($form->isSubmitted() && !$form->isValid()) {
            // return error code for modal and ok for non-modals
            $formView = $form->createView();
            return $this->render('admin/schools/units/addresses/form.html.twig', [
                'address' => $address,
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
        return $this->render('admin/schools/units/addresses/form.html.twig', [
            'address' => $address,
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
     * Controller for deleting a single school unit address (admin only).
     *
     * @Route("/api/v2/admin/schools/units/addresses/{id}/delete",
     *     name="api.admin_delete_school_unit_address",
     *     methods={"DELETE"})
     * @Route("/{_locale}/admin/schools/units/addresses/{id}/delete",
     *     options={"expose" = true},
     *     name="nav.admin_delete_school_unit_address")
     *
     * @SWG\Response(
     *     response=200,
     *     description="School unit address was deleted",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit address was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the school unit address"
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminDeleteSchoolUnitAddressAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var InvoiceAddress $address */
        $address = $em->getRepository(InvoiceAddress::class)->find($id);

        // handle rest calls
        if ($address && $this->isRestfulRequest($request)) {
            $em->remove($address);
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
            $name = $address->getName();

            $em->remove($address);
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.school_unit_address.deleted',
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse(['reloadPage' => 1], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_list_school_unit_addresses');
        }

        return $this->render('admin/schools/units/addresses/delete.html.twig', [
            'address' => $address,
            'form' => $form->createView(),
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }

}