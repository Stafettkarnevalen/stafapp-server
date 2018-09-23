<?php

namespace App\Controller\Security;

use App\Controller\Interfaces\ModalEventController;
use App\Controller\Traits\RESTFulControllerTrait;
use App\Controller\Traits\TableSortingControllerTrait;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Form\Group\EditType as GroupEditType;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use App\Entity\Api\Message as ApiMessage;

/**
 * Class AdminGroupController
 * - Handles Group entities.
 * - API calls for admin.
 * - Admin funtionality for UI.
 *
 * @package App\Controller\Security
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class AdminGroupController extends FOSRestController implements ModalEventController
{
    /** Use some REST methods */
    use RESTFulControllerTrait;

    /** Use table sorting */
    use TableSortingControllerTrait;

    /**
     * Controller for listing groups (admin only).
     * RESTful API Supported (GET).
     *
     * @Route("/api/v2/admin/groups",
     *     name="api.admin_list_groups",
     *     methods={"GET"})
     * @Route("/{_locale}/admin/groups/list",
     *     options={"expose" = true},
     *     name="nav.admin_list_groups")
     *
     * @SWG\Get(
     *     path="/api/v2/admin/groups",
     *     summary="Get groups",
     *     description="Return an array of groups",
     *     operationId="getGroups",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns a list of groups",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref=@Nelmio\Model(type=Group::class, groups={"SecurityApi","Default"}))
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return mixed
     */
    public function adminListGroupsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SecurityAPI', 'Default']);

        // handle sorting
        list($sort, $sortKey,$order, $orders) = $this->handleSorting(
            $request,
            ['name', 'email'],
            'admin_groups',
            'name');

        // fetch
        $groups = $em->getRepository(Group::class)->findBy([], $sort);

        // Return a RESTful JSON response or HTML
        $view = $this->view($groups, Response::HTTP_OK)
            ->setTemplate('admin/groups/list.html.twig')
            ->setTemplateVar('groups')
            ->setTemplateData([
                'orders' => $orders,
                'order' => $order,
                'sort' => $sortKey,
            ])
            ->setContext($ctx)
        ;

        return $this->handleView($view);
    }

    /**
     * Controller for reading a single group (admin only).
     * RESTful API only (GET).
     *
     * @Route("/api/v2/admin/groups/{id}",
     *     name="api.admin_read_group",
     *     methods={"GET"})
     *
     * @SWG\Get(
     *     path="/api/v2/admin/groups/{id}",
     *     summary="Get a group",
     *     description="Return a single group",
     *     operationId="getGroup",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns the group",
     *         @SWG\Schema(
     *             @Nelmio\Model(type=Group::class, groups={"SecurityApi","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NOT_FOUND,
     *         description="No group was found with id",
     *         @SWG\Schema(
     *             @Nelmio\Model(type=ApiMessage::class, groups={"SecurityApi","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the group"
     *     )
     * )
     *
     * @param integer $id
     * @return mixed
     */
    public function adminReadGroupRESTAction($id)
    {
        return $this->readEntity(Group::class, $id, ['SecurityAPI', 'Default']);
    }

    /**
     * Controller for patchin a single group (admin only).
     * RESTful API only (PATCH).
     *
     * @Route("/api/v2/admin/groups/{id}",
     *     name="api.admin_patch_group",
     *     methods={"PATCH"})
     *
     * @SWG\Patch(
     *     path="/api/v2/admin/groups/{id}",
     *     summary="Patch a group",
     *     description="Update a group",
     *     operationId="patchGroup",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns the group",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=Group::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NOT_FOUND,
     *         description="No group was found with the id",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_CONFLICT,
     *         description="The query paramters were not valid, the group could not be saved",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the group"
     *     )
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminPatchGroupRESTAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // Fetch
        /** @var Group $group */
        $group = $em->getRepository(Group::class)->find($id);
        if (!$group) {
            return $this->notFoundError('id', $id);
        }
        try {
            $group->fill($request->request->all());
            $em->merge($group);
            $em->flush();
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, Response::HTTP_CONFLICT);
        }
        return $this->readEntity(Group::class, $id, ['SecurityAPI', 'Default']);
    }

    /**
     * Controller for editing a single group (admin only).
     * RESTful API only (PUT).
     *
     * @Route("/api/v2/admin/groups/{id}",
     *     name="api.admin_edit_group",
     *     methods={"PUT"})
     *
     * @SWG\Put(
     *     path="/api/v2/admin/groups/{id}",
     *     summary="Update a group",
     *     description="Update a group",
     *     operationId="updateGroup",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns the group",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=Group::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NOT_FOUND,
     *         description="No group was found with the id",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_CONFLICT,
     *         description="The query paramters were not valid",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_BAD_REQUEST,
     *         description="The group could not be saved",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the group"
     *     )
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminEditGroupRESTAction($id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SecurityAPI', 'Default']);

        // Fetch or create the entity
        if ($id) {
            $group = $em->getRepository(Group::class)->find($id);
            if (!$group) {
                return $this->notFoundError('id', $id);
            }
        } else {
            $group = new Group();
        }

        $users = $em->getRepository(User::class)->findBy([], ['firstname' =>'ASC', 'lastname' => 'ASC']);

        $form = $this->createForm(GroupEditType::class, $group, [
            'available_users' => $users,
            'available_roles' => $this->getParameter('roles'),
            'csrf_protection' => false,
            'is_api' => true,
        ]);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            try {
                if ($group->getId())
                    $em->merge($group);
                else
                    $em->persist($group);
                $em->flush();

                return $this->readEntity(Group::class, $group->getId(), $ctx->getGroups(), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                // form was valid but an exception was thrown
                return $this->error($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
            }
        }

        // Form was not valid
        $err = $form->getErrors(true, true);
        $errorsList = [];
        foreach ($err as $it) {
            $errorsList[(string)$it->getOrigin()->getPropertyPath()] = $it->getMessage();
        }
        return $this->errors($errorsList, Response::HTTP_CONFLICT);
    }

    /**
     * Controller for creating a single group (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/groups",
     *     name="api.admin_create_group",
     *     methods={"POST"})
     *
     * @SWG\Post(
     *     path="/api/v2/admin/groups",
     *     summary="Create a group",
     *     description="Create a group",
     *     operationId="createGroup",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_CREATED,
     *         description="Returns the group",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=Group::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_FORBIDDEN,
     *         description="Another group was found with the same email",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_CONFLICT,
     *         description="The query paramters were not valid",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_BAD_REQUEST,
     *         description="The group could not be saved",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return mixed
     */
    public function adminCreateGroupRESTAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository(Group::class)->findOneBy(['email', $request->request->get('email')]);
        if ($group)
            return $this->badValueError('email', $request->request->get('email'), Response::HTTP_FORBIDDEN);
        return $this->adminEditGroupRESTAction(0, $request);
    }

    /**
     * Controller for creating or editing a single group (admin only).
     *
     * @Route("/{_locale}/admin/groups/group/create",
     *     options={"expose" = true},
     *     name="nav.admin_create_group")
     * @Route("/{_locale}/admin/groups/group/{id}/edit",
     *     options={"expose" = true},
     *     name="nav.admin_edit_group")
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminEditGroupAction($id = 0, Request $request) {
        $em = $this->getDoctrine()->getManager();

        if ($id)
            $group = $em->getRepository(Group::class)->find($id);
        else
            $group = new Group();

        $users = $em->getRepository(User::class)->findBy([], ['firstname' =>'ASC', 'lastname' => 'ASC']);
        $form = $this->createForm(GroupEditType::class, $group, [
            'available_users' => $users,
            'available_roles' => $this->getParameter('roles'),
            'attr' => ['action' => $request->getPathInfo()],
            'delete_title' => $this->get('translator')->trans('action.delete'),
            'delete_path' => $this->generateUrl('nav.admin_group_delete', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $action = $group->getId() ? 'updated' : 'saved';

            if ($group->getId())
                $em->merge($group);
            else
                $em->persist($group);

            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.group.' . $action,
                    'parameters' => ['%name%' => $group->getName()]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_groups');
        } else if ($form->isSubmitted() && !$form->isValid()) {
            // return error code for modal and ok for non-modals
            return $this->render('admin/groups/form.html.twig', [
                'group' => $group,
                'form' => $form->createView(),
                'modal' => $request->isXmlHttpRequest(),
            ], new Response('', $request->isXmlHttpRequest() ?
                Response::HTTP_BAD_REQUEST :
                Response::HTTP_OK));
        }

        $formView = $form->createView();
        return $this->render('admin/groups/form.html.twig', [
            'group' => $group,
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
            ,
        ]);
    }

    /**
     * Controller for deleting a single group (admin only).
     * RESTful API Supported (DELETE).
     *
     * @Route("/api/v2/admin/groups/{id}",
     *     name="api.admin_delete_group",
     *     methods={"DELETE"})
     * @Route("/{_locale}/admin/groups/{id}/delete",
     *     options={"expose" = true},
     *     name="nav.admin_delete_group")
     *
     * @SWG\Delete(
     *     path="/api/v2/admin/groups/{id}",
     *     summary="Delete a group",
     *     description="Delete a group",
     *     operationId="deleteGroup",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Group was deleted",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NOT_FOUND,
     *         description="No group was found with id",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_BAD_REQUEST,
     *         description="The group could not be deleted",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the group"
     *     )
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminDeleteGroupAction($id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Group $group */
        $group = $em->getRepository(Group::class)->find($id);

        // handle rest calls
        if ($group && $this->isRestfulRequest($request)) {

            try {
                $em->remove($group);
                $em->flush();
            } catch (\Exception $e) {
                $this->error($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
            }

            return $this->ok();

        } else if ($this->isRestfulRequest($request)) {
            return $this->notFoundError('id', $id);
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => 'group',
            'attr' => ['action' => $request->getPathInfo()]
        ]);
        $fb
            ->add('yes', SubmitType::class, ['left_icon' => 'fa-trash', 'right_icon' => 'fa-check', 'attr' => ['class' => 'btn-danger'], 'label' => 'label.yes']);

        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $name = $group->getName();

            $em->remove($group);
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.group.deleted',
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse(['reloadPage' => 1], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_groups');
        }

        return $this->render('admin/groups/delete.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }

}
