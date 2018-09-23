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
use App\Entity\Communication\Message;
use App\Entity\Communication\SchoolTypeDistribution;
use App\Entity\Schools\SchoolType;
use App\Form\SchoolType\EditType as SchoolTypeEditType;
use App\Repository\SchoolTypeRepository;
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
 * Class AdminSchoolTypeController
 * - Handles SchoolType entities connected to SchoolUnit entities.
 * - API calls for admin.
 * - Admin funtionality for UI.
 *
 * @package App\Controller\Schools
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class AdminSchoolTypeController extends FOSRestController implements ModalEventController
{
    /** Use some REST methods */
    use RESTFulControllerTrait;

    /** Use table sorting */
    use TableSortingControllerTrait;

    /**
     * Controller for listing school types (admin only).
     *
     * @Route("/api/v2/admin/schools/types/{group}/list",
     *     name="api.admin_list_school_types",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/schools/types/{group}/list",
     *     options={"expose"=true},
     *     name="nav.admin_list_school_types")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns a list of school types",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Nelmio\Model(type=SchoolType::class, groups={"SchoolApi","Default"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="group",
     *     in="path",
     *     type="integer",
     *     description="The id of the parent group"
     * )
     *
     * @param integer $move
     * @param integer $id
     * @param integer $group
     * @param Request $request
     * @return mixed
     */
    public function adminListSchoolTypesAction($move = 0, $id = 0, $group = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SchoolAPI', 'Default']);

        /** @var SchoolType $group */
        $group = $em->getRepository(SchoolType::class)->find($group);

        if (!$id && !$group)
            return $this->notFoundError('group', $group, Response::HTTP_NOT_FOUND);

        // toggle active / inactive
        if (!$move && $id) {
            /** @var SchoolType $type */
            $type = $em->getRepository(SchoolType::class)->find($id);
            if (!$type)
                return $this->notFoundError('id', $id);

            $type->setIsActive(!$type->getIsActive());
            $em->merge($type);
            $em->flush();

            if ($this->isRestfulRequest($request))
                return $this->readEntity(SchoolType::class, $id, ['SchoolAPI', 'Default']);

            $group = $type->getGroup();

            // handle move
        } else if ($move != 0 && $id != 0) {
            /** @var SchoolTypeRepository $repo */
            $repo = $em->getRepository(SchoolType::class);
            $type = $repo->find($id);

            if (!$type && $this->isRestfulRequest($request))
                return $this->notFoundError('id', $id);

            try {
                $oldOrder = $type->getOrder();
                $type->setOrder($oldOrder + $move);
                if ($move > 0) {
                    $from = $oldOrder + 1;
                    $until = $oldOrder + $move;
                    $countAll = count($repo->findBy(['group' => $group]));

                    if (($until > $countAll - 1) && $this->isRestfulRequest($request))
                        return $this->outOfBoundsError('move', $move);

                    $up = $repo->findByOrderBetween($from, $until, $group);
                    /** @var SchoolType $upfld */
                    foreach ($up as $upfld) {
                        $upfld->setOrder($upfld->getOrder() - 1);
                        $em->merge($upfld);
                    }
                    $em->merge($type);
                    $em->flush();
                } else {
                    $from = $oldOrder + $move;
                    $until = $oldOrder - 1;

                    if ($from < 0  && $this->isRestfulRequest($request))
                        return $this->outOfBoundsError('move', $move);

                    $down = $repo->findByOrderBetween($from, $until, $group);
                    /** @var SchoolType $downfld */
                    foreach ($down as $downfld) {
                        $downfld->setOrder($downfld->getOrder() + 1);
                        $em->merge($downfld);
                    }
                    $em->merge($type);
                    $em->flush();
                }
                if ($this->isRestfulRequest($request))
                    return $this->ok();
            } catch (\Exception $e) {
                if ($this->isRestfulRequest($request))
                    return $this->error($e->getMessage());
                return new JsonResponse(['error' => $e->getMessage(), 'status' => 'failure'], Response::HTTP_BAD_REQUEST);
            }
        }

        // handle sorting
        list($sort, $sortKey,$order, $orders) = $this->handleSorting(
            $request,
            ['name', 'order', 'minClassOf', 'maxClassOf', 'isActive'],
            'admin_school_types',
            'name',
            'ASC'
        );

        // fetch
        $schoolTypes = $em->getRepository(SchoolType::class)->findBy(['group' => $group], $sort);

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
                    'data-helpmode' => null,
                    'data-placement' => 'top',
                    'title' => 'action.close',
                    'data-content' => 'help.action.close.window',
                ],
                'label' => 'action.close',
            ]);

        $form = $fb->getForm();

        // Return a RESTful JSON response or HTML
        $view = $this->view($schoolTypes, Response::HTTP_OK)
            ->setTemplate('admin/schools/types/list.html.twig')
            ->setTemplateVar('schoolTypes')
            ->setTemplateData([
                'group' => $group,
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
     * Controller for toggling active / inactive state of one school type (admin only).
     *
     * @Route("/api/v2/admin/schools/types/{id}/toggle",
     *     name="api.admin_toggle_school_type",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/schools/types/{id}/toggle",
     *     options={"expose"=true},
     *     name="nav.admin_toggle_school_type")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school type",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolType::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school type was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminToggleSchoolTypeAction($id = 0, Request $request)
    {
        return $this->adminListSchoolTypesAction(0, $id, 0, $request);
    }

    /**
     * Controller for moving a school type (admin only).
     *
     * @Route("/api/v2/admin/schools/types/{id}/move/{move}",
     *     name="api.admin_move_school_type",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/schools/types/{id}/move/{move}",
     *     options={"expose"=true},
     *     name="nav.admin_move_school_type")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school type",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolType::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school type was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     *
     * @param integer $id
     * @param integer $move
     * @param Request $request
     * @return mixed
     */
    public function adminMoveSchoolTypeAction($id = 0, $move, Request $request)
    {
        return $this->adminListSchoolTypesAction($move, $id, 0, $request);
    }


    /**
     * Controller for reading a single school type (admin only).
     * RESTful API only (GET).
     *
     * @Route("/api/v2/admin/schools/types/{id}/read",
     *     name="api.admin_read_school_type",
     *     methods={"GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school type",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolType::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school type was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the school type"
     * )
     *
     * @param integer $id
     * @return mixed
     */
    public function adminReadSchoolTypeRESTAction($id)
    {
        return $this->readEntity(SchoolType::class, $id, ['SchoolAPI', 'Default']);
    }

    /**
     * Controller for editing a single school type (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/schools/types/{id}/edit",
     *     name="api.admin_edit_school_type",
     *     methods={"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school type",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolType::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school type was found with the id",
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
     *     description="The id of the school type"
     * )
     *
     * @param integer $id
     * @param integer $group
     * @param Request $request
     * @return mixed
     */
    public function adminEditSchoolTypeRESTAction($id = 0, $group = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SchoolAPI', 'Default']);

        // Fetch or create the entity
        if ($id) {
            $type = $em->getRepository(SchoolType::class)->find($id);
            if (!$type) {
                return $this->notFoundError('id', $id);
            }
        } else {
            $type = new SchoolType();
            $grp = $em->getRepository(SchoolType::class)->find($group);
            if ($group && !$grp)
                return $this->notFoundError('group', $group, Response::HTTP_NOT_FOUND);
            if ($grp)
                $type->setGroup($grp);
            $siblings = $type->getSiblings($em);
            $type->setOrder($siblings->count());
        }

        $form = $this->createForm(SchoolTypeEditType::class, $type, [
            'csrf_protection' => false,
            'is_api' => true,
        ]);

        $form->submit($request->request->all());

        if ($form->isValid() && $form->isSubmitted()) {
            if ($type->getId())
                $em->merge($type);
            else
                $em->persist($type);
            $em->flush();

            return $this->readEntity(SchoolType::class, $type->getId(), $ctx->getGroups());
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
     * Controller for creating a single school type (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/schools/types/{group}/create",
     *     name="api.admin_create_school_type",
     *     methods={"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the school type",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=SchoolType::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No parent school type was found with the group parameter",
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
     *     name="group",
     *     in="path",
     *     type="integer",
     *     description="The id of the parent group"
     * )
     *
     * @param integer $group
     * @param Request $request
     * @return mixed
     */
    public function adminCreateSchoolTypeRESTAction($group = 0, Request $request)
    {
        return $this->adminEditSchoolTypeRESTAction(0, $group, $request);
    }

    /**
     * Controller for creating or editing a single school type (admin only).
     * RESTful API separate (adminCreate- or adminEditSchoolTypeRESTAction).
     *
     * @Route("/{_locale}/admin/schools/types/{group}/create",
     *     options={"expose"=true},
     *     name="nav.admin_create_school_type")
     * @Route("/{_locale}/admin/schools/types/{id}/edit",
     *     options={"expose"=true},
     *     name="nav.admin_edit_school_type")
     * @param integer $id
     * @param integer $group
     * @param Request $request
     * @return mixed
     */
    public function adminCreateOrEditSchoolTypeAction($id = 0, $group = 0,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository(SchoolType::class)->find($group);
        if ($id) {
            $schoolType = $em->getRepository(SchoolType::class)->find($id);
        } else {
            $schoolType = new SchoolType();
            if ($group)
                $schoolType->setGroup($group);
            $siblings = $schoolType->getSiblings($em);
            $schoolType->setOrder($siblings->count());
        }

        $form = $this->createForm(SchoolTypeEditType::class, $schoolType, [
            'attr' => ['action' => $request->getPathInfo()],
            'delete_title' => $this->get('translator')->trans('label.delete', [], 'school'),
            'delete_path' => $this->generateUrl('nav.admin_delete_school_type', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Message $message */
            $message = null;
            if (($message = $schoolType->getMessage()) && $message->getText() && $message->getTitle() && count($message->getType())) {
                $message->setCreatedBy($this->getUser());
                $message->setDistribution(new SchoolTypeDistribution($schoolType));
            } else {
                $message = null;
            }

            $action = $schoolType->getId() ? 'updated' : 'saved';

            if ($schoolType->getId())
                $em->merge($schoolType);
            else
                $em->persist($schoolType);

            if ($message) {
                $em->persist($message);
            }
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.school_type.' . $action,
                    'parameters' => ['%name%' => $schoolType->getName()]
                ]);
            // return an empty response for the ajax modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_list_school_types');

        } else if ($form->isSubmitted() && !$form->isValid()) {
            // return error code for modal and ok for non-modals
            $formView = $form->createView();
            return $this->render('admin/schools/types/form.html.twig', [
                'schoolType' => $schoolType,
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
        return $this->render('admin/schools/types/form.html.twig', [
            'schoolType' => $schoolType,
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
     * Controller for deleting a single school type (admin only).
     *
     * @Route("/api/v2/admin/schools/types/{id}/delete",
     *     name="api.admin_delete_school_type",
     *     methods={"DELETE"})
     * @Route("/{_locale}/admin/schools/types/{id}/delete",
     *     options={"expose" = true},
     *     name="nav.admin_delete_school_type")
     *
     * @SWG\Response(
     *     response=200,
     *     description="School type was deleted",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No school type was found with id",
     *     @SWG\Schema(
     *         @Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The id of the school type"
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminDeleteSchoolTypeAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var SchoolType $schoolType */
        $schoolType = $em->getRepository(SchoolType::class)->find($id);

        // handle rest calls
        if ($schoolType && $this->isRestfulRequest($request)) {
            $em->remove($schoolType);
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
            $name = $schoolType->getName();

            $em->remove($schoolType);
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.school_type.deleted',
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse(['reload' => 1], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_list_school_types');
        }

        return $this->render('admin/schools/types/delete.html.twig', [
            'schoolType' => $schoolType,
            'form' => $form->createView(),
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }
}