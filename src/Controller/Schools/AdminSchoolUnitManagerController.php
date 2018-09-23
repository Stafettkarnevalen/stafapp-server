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
use App\Entity\Security\SchoolManagerPosition;
use App\Form\School\EditUnitManagerType as SchoolManagerPositionEditType;
use App\Repository\SchoolManagerPositionRepository;
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
 * Class AdminSchoolUnitManagerController
 * - Handles SchoolManager entities connected to a SchoolUnit entity.
 * - API calls for admin.
 * - Admin funtionality for UI.
 *
 * @package App\Controller\Schools
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class AdminSchoolUnitManagerController extends FOSRestController implements ModalEventController
{
    /** Use some REST methods */
    use RESTFulControllerTrait;

    /** Use table sorting */
    use TableSortingControllerTrait;

    /**
     * Controller for listing school unit managers (admin only).
     *
     * @Route("/api/v2/admin/schools/units/{schoolUnit}/managers/list",
     *     name="api.admin_list_school_unit_managers",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/schools/units/{schoolUnit}/managers/list",
     *     options={"expose"=true},
     *     name="nav.admin_list_school_unit_managers")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns a list of school unit managers",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Nelmio\Model(type=SchoolManagerPosition::class, groups={"SchoolApi","Default"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="schoolUnit",
     *     in="path",
     *     type="integer",
     *     description="The id of the connected school unit"
     * )
     *
     * @param integer $schoolUnit
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminListSchoolUnitManagersAction($schoolUnit = 0, $id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SchoolAPI', 'Default']);

        /** @var SchoolUnit $schoolUnit */
        $schoolUnit = $em->getRepository(SchoolUnit::class)->find($schoolUnit);
        if (!$id && !$schoolUnit)
            return $this->notFoundError('schoolUnit', $schoolUnit, Response::HTTP_NOT_FOUND);

        // toggle active / inactive state
        if ($id) {
            /** @var SchoolManagerPosition $manager */
            $manager = $em->getRepository(SchoolManagerPosition::class)->find($id);

            if (!$manager)
                return $this->notFoundError('id', $id);

            $manager->setIsActive(!$manager->getIsActive());
            $em->merge($manager);
            $em->flush();

            if ($this->isRestfulRequest($request))
                return $this->readEntity(SchoolManagerPosition::class, $id, ['SchoolAPI', 'Default']);

            $schoolUnit = $manager->getSchoolUnit();
        }

        // handle sorting
        list($sort, $sortKey,$order, $orders) = $this->handleSorting(
            $request,
            ['name', 'from', 'until', 'type', 'status'],
            'admin_school_unit_managers',
            'name',
            'ASC',
            ['name' => ['firstname', 'lastname']]
        );

        // fetch
        /** @var SchoolManagerPositionRepository $er */
        $er = $em->getRepository(SchoolManagerPosition::class);
        $managers = $er->findBySchoolUnit($schoolUnit, $sort);

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
        $view = $this->view($managers, Response::HTTP_OK)
            ->setTemplate('admin/schools/units/managers/list.html.twig')
            ->setTemplateVar('schoolManagers')
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
     * Controller for toggling active / inactive state of one school unit manager (admin only).
     *
     * @Route("/api/v2/admin/schools/units/managers/{id}/toggle",
     *     name="api.admin_toggle_school_unit_manager",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/schools/units/managers/{id}/toggle",
     *     options={"expose"=true},
     *     name="nav.admin_toggle_school_unit_manager")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit manager",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolManagerPosition::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit manager was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminToggleSchoolUnitManagersAction($id = 0, Request $request)
    {
        return $this->adminListSchoolUnitManagersAction(0, $id, $request);
    }

    /**
     * Controller for reading a single school unit manager (admin only).
     * RESTful API only (GET).
     *
     * @Route("/api/v2/admin/schools/units/managers/{id}/read",
     *     name="api.admin_read_school_unit_manager",
     *     methods={"GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit manager",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolManagerPosition::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit manager was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the school unit manager"
     * )
     *
     * @param integer $id
     * @return mixed
     */
    public function adminReadSchoolUnitManagerRESTAction($id)
    {
        return $this->readEntity(SchoolManagerPosition::class, $id, ['SchoolAPI', 'Default']);
    }

    /**
     * Controller for editing a single school unit manager (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/schools/units/managers/{id}/edit",
     *     name="api.admin_edit_school_unit_manager",
     *     methods={"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit manager",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolManagerPosition::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit manager was found with the id",
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
     *     description="The id of the school unit manager"
     * )
     *
     * @param integer $id
     * @param integer $schoolUnit
     * @param Request $request
     * @return mixed
     */
    public function adminEditSchoolUnitManagerRESTAction($id = 0, $schoolUnit = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SchoolAPI', 'Default']);

        // Fetch or create the entity
        if ($id) {
            $manager = $em->getRepository(SchoolManagerPosition::class)->find($id);
            if (!$manager) {
                return $this->notFoundError('id', $id);
            }
        } else {
            $manager = new SchoolManagerPosition();
            /** @var SchoolUnit $schoolUnit */
            $schoolUnit = $em->getRepository(SchoolUnit::class)->find($schoolUnit);
            if (!$schoolUnit)
                return $this->notFoundError('schoolUnit', $schoolUnit, Response::HTTP_NOT_FOUND);
            $manager->setSchoolUnit($schoolUnit);
        }

        $form = $this->createForm(SchoolManagerPositionEditType::class, $manager, [
            'csrf_protection' => false,
            'is_api' => true,
            'schoolUnit' => $schoolUnit,
        ]);

        $form->submit($request->request->all());

        if ($form->isValid() && $form->isSubmitted()) {
            if ($manager->getId())
                $em->merge($manager);
            else
                $em->persist($manager);
            $em->flush();

            return $this->readEntity(SchoolManagerPosition::class, $manager->getId(), $ctx->getGroups());
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
     * Controller for creating a single school unit manager (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/schools/units/{schoolUnit}/managers/create",
     *     name="api.admin_create_school_unit_manager",
     *     methods={"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school unit manager",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolManagerPosition::class, groups={"SchoolApi","Default"})
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
    public function adminCreateSchoolUnitManagerRESTAction($schoolUnit = 0, Request $request)
    {
        return $this->adminEditSchoolUnitManagerRESTAction(0, $schoolUnit, $request);
    }

    /**
     * Controller for creating or editing a single school unit manager (admin only).
     * RESTful API separate (adminCreateOrEditSchoolUnitManagerRESTAction).
     *
     * @Route("/{_locale}/admin/schools/units/managers/create/{schoolUnit}",
     *     options={"expose"=true},
     *     name="nav.admin_create_school_unit_manager")
     * @Route("/{_locale}/admin/schools/units/managers/edit/{id}",
     *     options={"expose"=true},
     *     name="nav.admin_edit_school_unit_manager")
     * @param integer $id
     * @param integer $schoolUnit
     * @param Request $request
     * @return mixed
     */
    public function adminCreateOrEditSchoolUnitManagerAction($id = 0, $schoolUnit = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var SchoolUnit $schoolUnit */
        $schoolUnit = $em->getRepository(SchoolUnit::class)->find($schoolUnit);

        if ($id) {
            $schoolManagerPosition = $em->getRepository(SchoolManagerPosition::class)->find($id);
        } else {
            $schoolManagerPosition = new SchoolManagerPosition();
            $schoolManagerPosition->setSchoolUnit($schoolUnit);
        }

        $form = $this->createForm(SchoolManagerPositionEditType::class, $schoolManagerPosition, [
            'attr' => ['action' => $request->getPathInfo()],
            'delete_title' => $this->get('translator')->trans('label.delete', [], 'school'),
            'delete_path' => $this->generateUrl('nav.admin_delete_school_unit_manager', ['id' => $id]),
            'schoolUnit' => $schoolUnit,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $action = $schoolManagerPosition->getId() ? 'updated' : 'saved';

            if ($schoolManagerPosition->getId()) {
                $em->merge($schoolManagerPosition);
            } else {
                $em->persist($schoolManagerPosition);
            }
            $em->flush();

            $name = $schoolManagerPosition->getManager() ?
                $schoolManagerPosition->getManager()->getFullname() :
                $schoolManagerPosition->getUsername();
            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.school_unit_manager.' . $action,
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the ajax modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_list_school_unit_managers');

        } else if ($form->isSubmitted() && !$form->isValid()) {
            // return error code for modal and ok for non-modals
            $formView = $form->createView();
            return $this->render('admin/schools/units/managers/form.html.twig', [
                'schoolManagerPosition' => $schoolManagerPosition,
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
        return $this->render('admin/schools/units/managers/form.html.twig', [
            'schoolManagerPosition' => $schoolManagerPosition,
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
     * Controller for deleting a single school unit manager (admin only).
     *
     * @Route("/api/v2/admin/schools/units/managers/{id}/delete",
     *     name="api.admin_delete_school_unit_manager",
     *     methods={"DELETE"})
     * @Route("/{_locale}/admin/schools/units/managers/{id}/delete",
     *     options={"expose" = true},
     *     name="nav.admin_delete_school_unit_manager")
     *
     * @SWG\Response(
     *     response=200,
     *     description="School unit manager was deleted",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school unit manager was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the school unit manager"
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminDeleteSchoolUnitManagerAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var SchoolManagerPosition $schoolManagerPosition */
        $schoolManagerPosition = $em->getRepository(SchoolManagerPosition::class)->find($id);

        // handle rest calls
        if ($schoolManagerPosition && $this->isRestfulRequest($request)) {
            $em->remove($schoolManagerPosition);
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
            $name = $schoolManagerPosition->getName();

            $em->remove($schoolManagerPosition);
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.school_unit_manager.deleted',
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse(['reloadPage' => 1], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_list_school_unit_managers');
        }

        return $this->render('admin/schools/units/managers/delete.html.twig', [
            'schoolManagerPosition' => $schoolManagerPosition,
            'form' => $form->createView(),
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }
}