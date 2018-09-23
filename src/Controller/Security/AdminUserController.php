<?php

namespace App\Controller\Security;

use App\Controller\Interfaces\ModalEventController;
use App\Controller\Traits\RESTFulControllerTrait;
use App\Controller\Traits\TableSortingControllerTrait;
use App\Entity\Communication\Message;
use App\Entity\Communication\UserDistribution;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Form\User\EditType as UserEditType;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use App\Entity\Api\Message as ApiMessage;
use App\Controller\Routes as Routes;

/**
 * Class AdminUserController
 * - Handles User entities.
 * - API calls for admin.
 * - Admin funtionality for UI.
 *
 * @package App\Controller\Security
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class AdminUserController extends FOSRestController implements ModalEventController
{
    /** Use some REST methods */
    use RESTFulControllerTrait;

    /** Use table sorting */
    use TableSortingControllerTrait;

    /**
     * Controller for listing users (admin only).

     * @Route("/api/v2/admin/users",
     *     name=Routes::api_admin_list_users,
     *     methods={"GET"})
     * @Route("/{_locale}/admin/users/list",
     *     options={"expose" = true},
     *     name=Routes::nav_admin_list_users)
     *
     * @SWG\Get(
     *     path="/api/v2/admin/users",
     *     summary="Get users",
     *     description="Return an array of users",
     *     operationId="getUsers",
     *     produces={"application/json"},
     *     @SWG\Response(
     *          response=Response::HTTP_OK,
     *          description="Returned an array of users",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(ref=@Nelmio\Model(type=User::class, groups={"Admin","Default"}))
     *          )
     *      )
     * )
     *
     * @param integer $id if provided, the user with this id is toggled between active and inactive state.
     * @param Request $request
     * @return mixed
     */
    public function adminListUsersAction($id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SecurityAPI', 'Default']);

        // Check if toggle is needed
        if ($id) {
            /** @var User $user */
            $user = $em->getRepository(User::class)->find($id);

            if (!$user && $this->isRestfulRequest($request))
                return $this->notFoundError('id', $id);

            try {
                $user->setIsActive(!$user->getIsActive());
                $em->merge($user);
                $em->flush();
            } catch (\Exception $e) {
                if ($this->isRestfulRequest($request))
                    return $this->error($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
            }

            if ($this->isRestfulRequest($request))
                return $this->readEntity(User::class, $id, ['SecurityAPI', 'Default']);
        }

        // handle sorting
        list($sort, $sortKey,$order, $orders) = $this->handleSorting(
            $request,
            ['name', 'username', 'phone', 'isActive'],
            'admin_users',
            'name',
            'ASC',
            ['name' => ['firstname', 'lastname']]
        );
        
        // fetch
        $users = $em->getRepository(User::class)->findBy([], $sort);

        // Return a RESTful JSON response or HTML
        $view = $this->view($users, Response::HTTP_OK)
            ->setTemplate('admin/users/list.html.twig')
            ->setTemplateVar('users')
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
     * Controller for toggling active / inactive state of one user (admin only).
     * RESTful API Supported (PATCH).
     *
     * @Route("/api/v2/admin/users/{id}/toggle",
     *     name=Routes::api_admin_toggle_user,
     *     methods={"PATCH"})
     * @Route("/{_locale}/admin/users/{id}/toggle",
     *     options={"expose" = true},
     *     name=Routes::nav_admin_toggle_user)
     *
     * @SWG\Patch(
     *     path="/api/v2/admin/users/{id}/toggle",
     *     summary="Toggles a users active state",
     *     description="Return the patched user",
     *     operationId="toggleUser",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns the toggled user",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=User::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NOT_FOUND,
     *         description="No user was found with id",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_BAD_REQUEST,
     *         description="The user could not be saved",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the user"
     *     )
     * )
     *
     * @param integer $id the user with this id is toggled between active and inactive state.
     * @param Request $request
     * @return mixed
     */
    public function adminToggleUserAction($id, Request $request)
    {
        return $this->adminListUsersAction($id, $request);
    }

    /**
     * Controller for reading a single user (admin only).
     * RESTful API only (GET).
     *
     * @Route("/api/v2/admin/users/{id}",
     *     name=Routes::api_admin_read_user,
     *     methods={"GET"})
     *
     * @SWG\Get(
     *     path="/api/v2/admin/users/{id}",
     *     summary="Get a user",
     *     description="Return a single user",
     *     operationId="getUser",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns the user",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=User::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NOT_FOUND,
     *         description="No user was found with id",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the user"
     *     )
     * )
     *
     * @param integer $id
     * @return mixed
     */
    public function adminReadUserRESTAction($id)
    {
        return $this->readEntity(User::class, $id, ['SecurityAPI', 'Default']);
    }

    /**
     * Controller for patchin a single user (admin only).
     * RESTful API only (PATCH).
     *
     * @Route("/api/v2/admin/users/{id}",
     *     name=Routes::api_admin_patch_user,
     *     methods={"PATCH"})
     *
     * @SWG\Patch(
     *     path="/api/v2/admin/users/{id}",
     *     summary="Patch a user",
     *     description="Update a user",
     *     operationId="patchUser",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns the user",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=User::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NOT_FOUND,
     *         description="No user was found with the id",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_CONFLICT,
     *         description="The query paramters were not valid, the user could not be saved",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the user"
     *     )
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminPatchUserRESTAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // Fetch 
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->notFoundError('id', $id);
        }
        try {
            $user->fill($request->request->all());
            $em->merge($user);
            $em->flush();    
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, Response::HTTP_CONFLICT);
        }
        return $this->readEntity(User::class, $id, ['SecurityAPI', 'Default']);
    }
    
    /**
     * Controller for editing a single user (admin only).
     * RESTful API only (PUT).
     *
     * @Route("/api/v2/admin/users/{id}",
     *     name=Routes::api_admin_edit_user,
     *     methods={"PUT"})
     *
     * @SWG\Put(
     *     path="/api/v2/admin/users/{id}",
     *     summary="Update a user",
     *     description="Update a user",
     *     operationId="updateUser",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns the user",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=User::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NOT_FOUND,
     *         description="No user was found with the id",
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
     *         description="The user could not be saved",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the user"
     *     )
     * )
     *
     * @param integer $id
     * @param string $discriminator
     * @param Request $request
     * @return mixed
     * @throws \ReflectionException
     */
    public function adminEditUserRESTAction($id = 0, $discriminator = 'SchoolManager', Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ctx = $this->getContext(['SecurityAPI', 'Default']);

        // Fetch or create the entity
        if ($id) {
            $user = $em->getRepository(User::class)->find($id);
            if (!$user) {
                return $this->notFoundError('id', $id);
            }
        } else {
            $ru = new \ReflectionClass("App\\Entity\\Security\\" . $discriminator);
            /** @var User $user */
            $user = $ru->newInstance();
            $user->setIsActive(true);
        }

        $groups = $em->getRepository(Group::class)->findBy([], ['name' =>'ASC']);

        $form = $this->createForm(UserEditType::class, $user, [
            'available_groups' => $groups,
            'available_roles' => $this->getParameter('roles'),
            'csrf_protection' => false,
            'is_api' => true,
        ]);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            if ($user->getPlainPassword()) {
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password)->setPasswordValidFor(-1);
            }

            try {
                if ($user->getId())
                    $em->merge($user);
                else
                    $em->persist($user);
                $em->flush();

                return $this->readEntity(User::class, $user->getId(), $ctx->getGroups(), Response::HTTP_CREATED);
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
     * Controller for creating a single user (admin only).
     * RESTful API only (POST).
     *
     * @Route("/api/v2/admin/users/{discriminator}",
     *     name=Routes::api_admin_create_user,
     *     methods={"POST"})
     *
     * @SWG\Post(
     *     path="/api/v2/admin/users/{discriminator}",
     *     summary="Create a user",
     *     description="Create a user",
     *     operationId="createUser",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_CREATED,
     *         description="Returns the user",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=User::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_FORBIDDEN,
     *         description="Another user was found with the same username or phone",
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
     *         description="The user could not be saved",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="discriminator",
     *         in="path",
     *         type="string",
     *         description="The entity class of the user"
     *     )
     * )
     *
     * @param string $discriminator
     * @param Request $request
     * @return mixed
     * @throws \ReflectionException
     */
    public function adminCreateUserRESTAction($discriminator, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['username', $request->request->get('username')]);
        if ($user)
            return $this->badValueError('username', $request->request->get('username'), Response::HTTP_FORBIDDEN);
        $user = $em->getRepository(User::class)->findOneBy(['phone', $request->request->get('phone')]);
        if ($user)
            return $this->badValueError('phone', $request->request->get('phone'), Response::HTTP_FORBIDDEN);
        return $this->adminEditUserRESTAction(0, $discriminator, $request);
    }

    /**
     * Controller for creating or editing a single user (admin only).
     *
     * @Route("/{_locale}/admin/users/{discriminator}/create",
     *     options={"expose" = true},
     *     name=Routes::nav_admin_create_user)
     * @Route("/{_locale}/admin/users/{id}/edit",
     *     options={"expose" = true},
     *     name=Routes::nav_admin_edit_user)
     * @param integer $id
     * @param string $discriminator
     * @param Request $request
     * @return mixed
     * @throws \ReflectionException
     */
    public function adminCreateOrEditUserAction($id = 0, $discriminator = 'SchoolManager', Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id) {
            $user = $em->getRepository(User::class)->find($id);
        } else {
            $r = new \ReflectionClass("App\\Entity\\Security\\" . $discriminator);
            /** @var User $user */
            $user = $r->newInstance();
            $user->setIsActive(true);
        }

        $groups = $em->getRepository(Group::class)->findBy([], ['name' =>'ASC']);
        $form = $this->createForm(UserEditType::class, $user, [
            'available_groups' => $groups,
            'available_roles' => $this->getParameter('roles'),
            'attr' => ['action' => $request->getPathInfo() . ($id ? '' : '?class=' . $request->get('class')) ],
            'delete_title' => $this->get('translator')->trans('label.delete', [], 'user'),
            'delete_path' => $this->generateUrl(Routes::nav_admin_delete_user, ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getPlainPassword()) {
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password)->setPasswordValidFor(-1);
            }

            /** @var Message $message */
            $message = null;
            if (($message = $user->getMessage()) && $message->getText() && $message->getTitle() && count($message->getType())) {
                $message->setCreatedBy($this->getUser());
                $message->setDistribution(new UserDistribution($user));
            } else {
                $message = null;
            }

            $action = $user->getId() ? 'updated' : 'saved';

            if ($user->getId())
                $em->merge($user);
            else
                $em->persist($user);

            if ($message) {
                $em->persist($message);
            }
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.user.' . $action,
                    'parameters' => ['%name%' => $user->getFullname()]
                ]);
            // return an empty response for the ajax modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_users');

        } else if ($form->isSubmitted() && !$form->isValid()) {
            // return error code for modal and ok for non-modals
            $formView = $form->createView();
            return $this->render('admin/users/form.html.twig', [
                'user' => $user,
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
        return $this->render('admin/users/form.html.twig', [
            'user' => $user,
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
     * Controller for deleting a single user (admin only).
     * RESTful API Supported (DELETE).
     *
     * @Route("/api/v2/admin/users/{id}",
     *     name=Routes::api_admin_delete_user,
     *     methods={"DELETE"})
     * @Route("/{_locale}/admin/users/{id}/delete",
     *     options={"expose" = true},
     *     name=Routes::nav_admin_delete_user)
     *
     * @SWG\Delete(
     *     path="/api/v2/admin/users/{id}",
     *     summary="Delete a user",
     *     description="Delete a user",
     *     operationId="deleteUser",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="User was deleted",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_NOT_FOUND,
     *         description="No user was found with id",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"SchoolApi","Default"})
     *         )
     *     ),
     *     @SWG\Response(
     *         response=Response::HTTP_BAD_REQUEST,
     *         description="The user could not be deleted",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=ApiMessage::class, groups={"Admin","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the user"
     *     )
     * )
     *
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminDeleteUserAction($id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($id);

        // handle rest calls
        if ($user && $this->isRestfulRequest($request)) {

            try {
                $em->remove($user);
                $em->flush();
            } catch (\Exception $e) {
                $this->error($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
            }

            return $this->ok();

        } else if ($this->isRestfulRequest($request)) {
            return $this->notFoundError('id', $id);
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => 'user',
            'attr' => ['action' => $request->getPathInfo()]
        ]);
        $fb
            ->add('yes', SubmitType::class, ['left_icon' => 'fa-trash', 'right_icon' => 'fa-check', 'attr' => ['class' => 'btn-danger'], 'label' => 'label.yes']);

        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $name = $user->getFullname();

            $em->remove($user);
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.user.deleted',
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse(['reloadPage' => 1], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_users');
        }

        return $this->render('admin/users/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }

    /** @var string Cookie to store admin session into  */
    const ADMIN_SESSION_COOKIE = 'AdminSessionCookie';

    /** @var string Cookie to store admin url into */
    const ADMIN_URL_REFERER = 'AdminUrlReferer';

    /**
     * Simulates a user login for the admin. The admin gets the session back when the simulated user logs out.
     * Not RESTful.
     *
     * @Route("/{_locale}/admin/users/{id}/simulate",
     *     name=Routes::nav_admin_simulate_user)
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminSimulateAction($id = 0, Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        if ($id) {
            /** @var User $user */
            $user = $em->getRepository(User::class)->find($id);
            if (!$user) {
                return $this->render('admin/index.html.twig');
            }
            $session->set(self::ADMIN_URL_REFERER, $request->server->get('HTTP_REFERER'));
            $dir = $this->getParameter('admin_files');
            @mkdir($dir);
            $file = tempnam($dir, '' . time());
            file_put_contents($file, serialize($session->all()));
            setcookie(self::ADMIN_SESSION_COOKIE, $file, 0, '/');

            // Logout the admin
            $request->getSession()->invalidate();

            // login the user
            $token = new UsernamePasswordToken(
                $user,
                $user->getPassword(),
                'main',
                $user->getRoles()
            );
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            // fire login event
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            return $this->redirect('/');
        }
        return $this->render('admin/index.html.twig');
    }
}
