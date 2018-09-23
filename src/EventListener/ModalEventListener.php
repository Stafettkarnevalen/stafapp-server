<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 21.19
 */

namespace App\EventListener;

use App\Controller\ModalEventController;
use App\Entity\Security\SystemUser;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ModalEventListener
 * @package AppBundle\EventListener
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class ModalEventListener
{
    /** @var TokenStorageInterface $tokenStorage The token storage */
    private $tokenStorage;

    /** @var  ContainerInterface $container The container*/
    private $container;

    /**
     * ModalEventListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param ContainerInterface $container
     */
    public function __construct(TokenStorageInterface $tokenStorage, ContainerInterface $container)
    {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
    }

    /**
     * Performs operations on kernel request.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        //$kernel    = $event->getKernel();
        //$request   = $event->getRequest();
    }

    /**
     * Performs operations on kernel response.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        $controller = $request->attributes->get('_controller');
        if (strpos($controller, '::') !== false) {

            list($controllerClass) = explode('::', $controller, 2);
            $controllerInstance = new $controllerClass();

            // logged on users can be redirected modally to passwd if password has expired
            if ($controllerInstance instanceof ModalEventController && $this->tokenStorage->getToken() &&
                ($user = $this->tokenStorage->getToken()->getUser()) && $user instanceof User) {
                if ($user->getPasswordValidFor() == 0 && $request->get('_route') == 'nav.passwd') {
                    /** @var Session $session */
                    $session = $request->getSession();
                    $session->getFlashBag()->add('info', 'flash.security.password_must_change');
                }
            }
        }
    }

    /**
     * Performs operations on kernel controller.
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $needsSetup = !(
            $em->getRepository(SystemUser::class)->find(1) &&
            $em->getRepository(SystemUser::class)->find(2));

        $request = $event->getRequest();

        $controller = $request->attributes->get('_controller');
        if (strpos($controller, '::') !== false) {

            list($controllerClass) = explode('::', $controller, 2);
            $controllerInstance = new $controllerClass();

            // if the system was not setup properly, redirect to setup form
            if ($controllerInstance instanceof ModalEventController &&
                $needsSetup && $request->get('_route') != 'nav.setup') {
                $event->setController(function () use ($request) {
                    return new RedirectResponse("/{$request->getLocale()}/setup");
                });
            }

            // logged on users can be redirected modally to passwd if password has expired
            if ($controllerInstance instanceof ModalEventController && $this->tokenStorage->getToken() &&
                ($user = $this->tokenStorage->getToken()->getUser()) && $user instanceof User) {

                if ($user->getPasswordValidFor() == 0 &&
                    $request->get('_route') != 'nav.passwd' &&
                    $request->get('_route') != 'root') {
                    $event->setController(function () use ($request) {
                        return new RedirectResponse("/{$request->getLocale()}/user/passwd");
                    });
                }
            }
        }
    }
}