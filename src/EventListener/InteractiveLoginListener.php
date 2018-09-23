<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 10/12/2016
 * Time: 13.35
 */

namespace App\EventListener;


use App\Entity\Security\PrincipalUser;
use App\Entity\Security\SchoolAdministrator;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class InteractiveLoginListener
 * @package App\EventListener
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class InteractiveLoginListener
{
    /** @var Session $session The session */
    private $session;

    /** @var Registry $registry The registry */
    private $registry;

    /** @var AuthorizationChecker $authorizationChecker The authorization checker */
    private $authorizationChecker;

    /**
     * InteractiveLoginListener constructor.
     *
     * @param AuthorizationChecker $authorizationChecker
     * @param Registry $registry
     * @param Session $session
     */
    public function __construct(AuthorizationChecker $authorizationChecker, Registry $registry, Session $session)
    {
        $this->session = $session;
        $this->registry = $registry;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Performs operations on an interactive login event.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->session->clear();

        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();

        /** update last login */
        $user->setLastLogin(new \DateTime('now'));
        $remoteHost = $event->getRequest()->getClientIp();
        $user->incrementLogins($remoteHost);

        // if password is temporary and valid for a fixed time of logins, decrease the times
        if (($validFor = $user->getPasswordValidFor()) > 0)
            $user->setPasswordValidFor($validFor - 1);

        $em = $this->registry->getManager();
        $em->clear();
        $em->merge($user);
        $em->flush();

        /** store locale in session */
        if (null !== $user->getLocale()) {
            $this->session->set('_locale', $user->getLocale());
        }

        if (!($user instanceof SchoolAdministrator))
            $this->session->getFlashBag()->set('success', [[
                'id' => (($user->getLogins() > 1) ? 'flash.user.welcome_back' : 'flash.user.welcome'),
                'parameters' => ['%name%' => $user->getFullname()]
            ]]);

        /*if ($user->hasRole(Group::ROLE_ADMIN)) {
            $schools = $this->registry->getEntityManager()->getRepository('App:School')->findAll();
            usort($schools, function ($a, $b) { return strcmp($a->getName(), $b->getName()); });
            $this->session->set('_schools', $schools);
            // $user->setSchools($this->registry->getRepository('App:School')->findAll());
        }*/
    }
}