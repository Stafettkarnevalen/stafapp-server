<?php

namespace App\Controller\Security;

use App\Controller\Interfaces\ModalEventController;
use App\Entity\Clients\MobileAppTicket;
use App\Entity\Communication\Message;
use App\Entity\Communication\Thread;
use App\Entity\Communication\UserDistribution;
use App\Entity\Security\Group;
use App\Entity\Security\SchoolManager;
use App\Entity\Security\User;
use App\Entity\Security\UserLogEvent;
use App\Entity\Security\UserProfile;
use App\Form\MobileApp\MobileAppLoginType;
use App\Form\User\ChangePasswordType;
use App\Form\User\EditType as UserEditType;
use App\Form\Group\EditType as GroupEditType;
use App\Form\User\OAuthRegistrationType;
use App\Form\User\TicketLoginType;
use App\Form\User\TicketPasswordType;
use App\Form\User\UserRecoveryType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Entity\Security\UserTicket;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Class SecurityController
 *
 * Takes care of login, password and other security procedures.
 *
 * @package App\Controller
 */
class SecurityController extends Controller implements ModalEventController
{
    /**
     * @var array Login types provided by the service.
     */
    private $loginTypes = [
        'traditional' => ['user-o', 'login.w_userpass', 'login.info_userpass', 'nav.login', 'security/login/traditional_form.html.twig'],
        'mobile-app' => ['mobile', 'login.w_mobile_app', 'login.info_mobile_app', 'nav.login_mobile_app', 'security/login/mobile_app_form.html.twig'],
        'facebook' => ['facebook', 'login.w_facebook', 'login.info_facebook', 'nav.login_facebook', 'security/login/oauth_form.html.twig'],
        'google' => ['google', 'login.w_google', 'login.info_google', 'nav.login_google', 'security/login/oauth_form.html.twig'],
        'instagram' => ['instagram', 'login.w_instagram', 'login.info_instagram', 'nav.login_instagram', 'security/login/oauth_form.html.twig'],
        'twitter' => ['twitter', 'login.w_twitter', 'login.info_twitter', 'nav.login_twitter', 'security/login/oauth_form.html.twig'],
        'username' => ['envelope-o', 'login.w_email', 'login.info_email', 'nav.login_email', 'security/login/ticket_form.html.twig'],
        'phone' => ['commenting-o', 'login.w_phone', 'login.info_phone', 'nav.login_phone', 'security/login/ticket_form.html.twig'],
        'usb' => ['key', 'login.w_usb', 'login.info_usb', 'nav.login_usb', 'security/login/ticket_form.html.twig'],
        'recovery' => ['recycle', 'login.forgot_password', 'login.info_forgot_password', 'nav.forgot', 'security/login/forgot_form.html.twig']
    ];

    /**
     * @var array Change password types provided by the service.
     */
    private $passwdTypes = [
        'traditional' => ['user-o', 'passwd.w_userpass', 'passwd.info_userpass', 'nav.passwd', 'security/password/traditional_form.html.twig'],
        'username' => ['envelope-o', 'passwd.w_email', 'passwd.info_email', 'nav.passwd_email', 'security/password/ticket_form.html.twig'],
        'phone' => ['commenting-o', 'passwd.w_phone', 'passwd.info_phone', 'nav.passwd_phone', 'security/password/ticket_form.html.twig'],
    ];

    /**
     * Controller for all oauth registrations.
     *
     * @Route("/oauth/register", name="nav.oauth_register_redirect")
     * @param Request $request
     * @return mixed
     */
    public function redirectOAuthAction(Request $request)
    {
        $locale = $request->getSession()->get('_locale', 'sv');
        return $this->redirectToRoute('nav.oauth_register', ['_locale' => $locale]);
    }

    /**
     * Controller for all oauth registrations.
     *
     * @Route("/{_locale}/oauth/register", name="nav.oauth_register")
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function registerOAuthAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $service = $request->getSession()->get('_oauth_type');

        /** @var User $user */
        $user = $request->getSession()->get('_oauth_user');

        $existing = $em->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]);
        if ($existing === null && $user->getPhone() !== null)
            $existing = $em->getRepository(User::class)->findOneBy(['phone' => $user->getPhone()]);

        if ($existing !== null) {
            $setter = 'set' . ucfirst($service) . 'Id';
            $getter = 'get' . ucfirst($service) . 'Id';
            $existing->$setter($user->$getter());

            $setter = 'set' . ucfirst($service) . 'AccessToken';
            $getter = 'get' . ucfirst($service) . 'AccessToken';
            $existing->$setter($user->$getter());

            $user = $existing;
        }

        $form = $this->createForm(OAuthRegistrationType::class, $user, ['service' => $service]);
        $form->handleRequest($request);
        $getter = 'get' . ucfirst($service) . 'AccessToken';
        $accessToken = $user->$getter();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($user->getId() === null) {
                $user
                    ->setPasswordValidFor(-1)
                    ->setGroups(new ArrayCollection())
                    ->setRoles([User::ROLE_DEFAULT])
                    ->setConsented(true)
                    ->setLocale($request->getLocale())
                ;
                $em->persist($user);
            } else if ($this->get('security.password_encoder')->isPasswordValid($user, $form->get('plainPassword')->getData())) {
                $em->persist($user);
            } else {
                $trans = $this->get('translator');
                $form->get('plainPassword')->addError(new FormError($trans->trans('label.invalid_password', [], 'user')));
                $formView = $form->createView();
                return $this->render('security/register/oauth.html.twig', [
                    'user' => $user,
                    'form' => $formView,
                    'typeId' => $formView->offsetGet($service . 'Id'),
                    'typeAccessToken' => $formView->offsetGet($service . 'AccessToken'),
                    'service' => $service,
                ]);
            }
            $em->flush();
            $serviceProviders = [
                'googleplus' => 'Google',
                'twitter' => 'Twitter',
                'facebook' => 'Facebook',
                'instagram' => 'Instagram'
            ];

            $this->get('session')->getFlashBag()->add('success', [
                'id' => 'flash.oauth.registered',
                'parameters' => ['%name%' => $serviceProviders[$service]]
            ]);

            $token = new OAuthToken($accessToken, $user->getRoles());
            $token->setResourceOwnerName($service);
            $token->setUser($user);
            $token->setAuthenticated(true);

            $this->get('security.token_storage')->setToken($token);

            $this->container->get('event_dispatcher')->dispatch(
                SecurityEvents::INTERACTIVE_LOGIN,
                new InteractiveLoginEvent($request, $token)
            );

            $request->getSession()->remove('_oauth_user');
            $request->getSession()->remove('_oauth_type');

            return $this->redirect('/');

        } else if ($form->isSubmitted() && !$form->isValid()) {
            $username = $form->getData()->getUsername();
            $phone = $form->getData()->getPhone();

            $existing = $em->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($existing === null && $phone !== null)
                $existing = $em->getRepository(User::class)->findOneBy(['phone' => $phone]);

            if ($existing !== null) {
                $setter = 'set' . ucfirst($service) . 'Id';
                $getter = 'get' . ucfirst($service) . 'Id';
                $existing->$setter($user->$getter());

                $setter = 'set' . ucfirst($service) . 'AccessToken';
                $getter = 'get' . ucfirst($service) . 'AccessToken';
                $existing->$setter($user->$getter());
                $user = $existing;

                $request->getSession()->set('_oauth_user', $user);
                return $this->redirectToRoute('nav.oauth_register');
            }
        }
        $formView = $form->createView();
        return $this->render('security/register/oauth.html.twig', [
            'user' => $user,
            'form' => $formView,
            'typeId' => $formView->offsetGet($service . 'Id'),
            'typeAccessToken' => $formView->offsetGet($service . 'AccessToken'),
            'service' => $service,
        ]);
    }

    /**
     * Controller for oauth login with twitter.
     *
     * @Route("/{_locale}/login/twitter/{userClass}", name="nav.login_twitter", defaults={"_locale"="sv"},
     *     requirements={"_locale"="sv|en|fi"})
     * @param string $userClass
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginTwitterAction($userClass = SchoolManager::class, Request $request)
    {
        $request->getSession()->set('_oauth_user', null);
        $request->getSession()->set('_oauth_type', null);
        return $this->redirectToRoute('nav.login_oauth', ['type'=>'twitter', 'userClass' => $userClass]);
    }

    /**
     * Controller for oauth login with instagram.
     *
     * @Route("/{_locale}/login/instagram/{userClass}", name="nav.login_instagram", defaults={"_locale"="sv"},
     *     requirements={"_locale"="sv|en|fi"})
     * @param string $userClass
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginInstagramAction($userClass = SchoolManager::class, Request $request)
    {
        $request->getSession()->set('_oauth_user', null);
        $request->getSession()->set('_oauth_type', null);
        return $this->redirectToRoute('nav.login_oauth', ['type'=>'instagram', 'userClass' => $userClass]);
    }

    /**
     * Controller for oauth login with google.
     *
     * @Route("/{_locale}/login/google/{userClass}", name="nav.login_google", defaults={"_locale"="sv"},
     *     requirements={"_locale"="sv|en|fi"})
     * @param string $userClass
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginGoogleAction($userClass = SchoolManager::class, Request $request)
    {
        $request->getSession()->set('_oauth_user', null);
        $request->getSession()->set('_oauth_type', null);
        return $this->redirectToRoute('nav.login_oauth', ['type'=>'googleplus', 'userClass' => $userClass]);
    }

    /**
     * Controller for oauth login with facebook.
     *
     * @Route("/{_locale}/login/facebook/{userClass}", name="nav.login_facebook", defaults={"_locale"="sv"},
     *     requirements={"_locale"="sv|en|fi"})
     * @param string $userClass
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginFacebookAction($userClass = SchoolManager::class, Request $request)
    {
        $request->getSession()->set('_oauth_user', null);
        $request->getSession()->set('_oauth_type', null);
        return $this->redirectToRoute('nav.login_oauth', ['type'=>'facebook', 'userClass' => $userClass]);
    }

    /**
     * Controller for all oauth logins.
     *
     * @Route("/{_locale}/login/oauth/{type}/{userClass}", name="nav.login_oauth", defaults={"_locale"="sv"},
     *     requirements={"_locale"="sv|en|fi"})
     * @param string $type the type of ticket to use (username|phone)
     * @param string $userClass
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function loginOAuthAction($type, $userClass, Request $request)
    {
        $request->getSession()->set('_oauth_user_class', $userClass);
        return $this->redirect('/oauth/connect/' . $type);
    }

    /**
     * Controller for resetting user passwords.
     *
     * @Route("/{_locale}/login/forgot", name="nav.forgot")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function forgotPasswordAction(Request $request)
    {
        $errors = [];
        $user = null;
        $em = $this->getDoctrine()->getManager();
        $phase = 'user';

        // create form and check if it was submitted
        $form = $this->createForm(UserRecoveryType::class, new User(), ['phase' => $phase]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // get the phase of the process
            $phase = $form->get('phase')->getData();
            // if first phase = get user by email
            if ($phase == 'user') {
                /** @var User $user */
                $user = $em->getRepository('App:Security\User')->findOneBy(['email' => $form->get('email')->getData(), 'isActive' => true]);
                if ($user) {
                    $phase = 'hash';
                    // create and send hash
                    $user->setEmailhash(User::createHash());
                    $em->persist($user);
                    $em->flush();

                    // email hash to user
                    // XXX TODO

                    // update form
                    $form = $this->createForm(UserRecoveryType::class, $user, ['phase' => $phase]);
                } else {
                    // user was not found = invalid email

                    $errors[] = new CustomUserMessageAuthenticationException('user.invalid_email');
                }
            // if second phase = login with hash
            } else if ($phase == 'hash') {
                // get the user again
                /** @var User $user */
                $user = $em->getRepository(User::class)->findOneBy(['email' => $form->get('email')->getData(), 'isActive' => true]);

                // update the form and see if it is submitted
                $form = $this->createForm(UserRecoveryType::class, $user, ['phase' => $phase]);
                $form->handleRequest($request);

                // if form was valid, hash was checked by constraint so invalidate hash and save new password
                /** @var SubmitButton $submitBtn */
                $submitBtn = $form->get('submit');
                /** @var SubmitButton $resendBtn */
                $resendBtn = $form->get('resend');
                if ($form->isValid() && $submitBtn->isClicked()) {
                    $user->setEmailhash(null);

                    $password = $this->get('security.password_encoder')
                        ->encodePassword($user, $user->getPlainPassword());
                    $user->setPassword($password);

                    $em->merge($user);
                    $em->flush();

                    // set message and redirect to login page
                    $this->get('session')->getFlashBag()->add('success', 'flash.password.reset');

                    return $this->redirectToRoute('nav.login');
                } else if ($form->isValid() && $resendBtn->isClicked()) {
                    // resend the hash
                    // XXX TODO

                } else if (!$form->isValid() && $submitBtn->isClicked()) {
                    foreach ($form->getErrors() as $error) {
                        $key = $error->getMessageTemplate();

                        if ($key == 'invalid.emailhash' || $key == 'invalid.phonehash') {
                            $log = new UserLogEvent();
                            $log->setUser($user)->setType(UserLogEvent::TYPE_PASSWORD)
                                ->setLevel(UserLogEvent::LEVEL_WARNING)->setResult($key)
                                ->setRemoteHost($request->getClientIp())->setTimestamp(new \DateTime('now'));
                            $em->persist($log);
                            $em->flush();
                        } else {
                            $log = new UserLogEvent();
                            $log->setUser($user)->setType(UserLogEvent::TYPE_PASSWORD)
                                ->setLevel(UserLogEvent::LEVEL_WARNING)->setResult("password.missmatch")
                                ->setRemoteHost($request->getClientIp())->setTimestamp(new \DateTime('now'));
                            $em->persist($log);
                            $em->flush();
                        }
                    }
                    $log = new UserLogEvent();
                    $log->setUser($user)->setType(UserLogEvent::TYPE_PASSWORD)
                        ->setLevel(UserLogEvent::LEVEL_WARNING)->setResult("password.missmatch")
                        ->setRemoteHost($request->getClientIp())->setTimestamp(new \DateTime('now'));
                    $em->persist($log);
                    $em->flush();
                }
            }
        }

        return $this->render('security/login/login.html.twig', [
            'types' => $this->loginTypes,
            'type' => 'recovery',
            'form' => $form->createView(),
            'errors' => $errors,
            'user' => $user,
            'phase' => $phase,
        ]);
    }

    /**
     * Controller for mobile app ticket login.
     *
     * @Route("/{_locale}/login/app/{id}", name="nav.login_mobile_app")
     * @param integer $id
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function loginMobileAppAction($id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->clear();

        $ticket = $em->getRepository(MobileAppTicket::class)->find($id);

        $error = null;

        if (!$id) {
            $ticket = new MobileAppTicket();
        }
        $phase = ($ticket->getId() ? 'login' : 'ticket');

        $form = $this->createForm(MobileAppLoginType::class, $ticket, [
            'phase' => $phase,
        ]);
        $form->handleRequest($request);

        // Check if ticket was changed to active state
        if ($request->isXmlHttpRequest() && $ticket->getIsActive()) {
            $user = $ticket->getMobileApp()->getUser();

            // login user
            $token = new UsernamePasswordToken(
                $user,
                $user->getPassword(),
                'main',
                $user->getRoles()
            );

            // login the user
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            // fire login event
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            return new JsonResponse(['stamped' => true, 'user' => $user->getId(), 'redirect' => $this->generateUrl('root')], Response::HTTP_OK);

        } else if ($request->isXmlHttpRequest()) {

            // wait for ticket to get stamped or run out
            return new JsonResponse(['stamped' => false, 'lifetime' => $ticket->getTTL()], Response::HTTP_OK);

        } else if ($form->isSubmitted() && $phase === 'ticket') {
            /** @var User $user */
            $user = $em->getRepository(User::class)->findOneBy(['username' => $form->get('username')->getData()]);
            if ($user) {
                $mobileApp = $user->getMobileApp();
                if ($mobileApp) {
                    /** @var \DateTime $until */
                    $until = clone $ticket->getFrom();
                    $until->add(new \DateInterval("PT3M"));
                    $ticket->setMobileApp($mobileApp)->setUntil($until);
                    $em->persist($ticket);
                    $em->flush();

                    // redirect to phase = login page
                    return $this->redirectToRoute('nav.login_mobile_app', ['id' => $ticket->getId()]);
                } else {
                    $error = new CustomUserMessageAuthenticationException('ticket.mobile_app_missing');
                }
            } else {
                $error = new CustomUserMessageAuthenticationException('ticket.user_not_found');
            }
        } else if ($form->isSubmitted() && $phase === 'login') {
            // cancel ticket
            /** @var SubmitButton $cancelBtn */
            $cancelBtn = $form->get('cancel');
            if ($cancelBtn->isClicked()) {
                $ticket->setUntil($ticket->getFrom());
                $em->merge($ticket);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'flash.ticket.invalidated');
            }

            // refresh ticket
            /** @var SubmitButton $refreshBtn */
            $refreshBtn = $form->get('refresh');
            if ($refreshBtn->isClicked()) {
                $now = new \DateTime();
                $now->add(new \DateInterval("PT3M"));
                $ticket->setUntil($now);
                $em->merge($ticket);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'flash.ticket.refreshed');
            }
        }

        return $this->render('security/login/login.html.twig', [
            'error' => $error,
            'types' => $this->loginTypes,
            'type' => 'mobile-app',
            'form' => $form->createView(),
            'ticket' => $ticket
        ]);
    }

    /**
     * Controller for ticket login with an email address.
     *
     * @Route("/{_locale}/login/email", name="nav.login_email")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginEmailAction()
    {
        return $this->redirectToRoute('nav.login_ticket', ['type'=>'username']);
    }

    /**
     * Controller for ticket login with a phone.
     *
     * @Route("/{_locale}/login/phone", name="nav.login_phone")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginPhoneAction()
    {
        return $this->redirectToRoute('nav.login_ticket', ['type'=>'phone']);
    }

    /**
     * Controller for ticket login with a USB-key.
     *
     * @Route("/{_locale}/login/usb/{secret}/{number}/{password}", name="nav.login_usb")
     * @param string $secret secret key stored on USB-key to log in the user with
     * @param string $number
     * @param string $password
     * @param Request $request
     * @return mixed
     */
    public function loginUSBAction($secret = null, $number = null, $password = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $error = null;
        $user = null;
        $ticket = null;

        /** @var UserTicket $ticket */
        if ($secret && ($ticket = $em->getRepository(UserTicket::class)->findOneBy([
                'ticket' => urldecode($secret), 'isActive' => true])) && $ticket->isCurrent() && $ticket->getIsActive()) {

            // login successful, invalidate ticket if not a permanent USB-key ticket
            if ($ticket->getType() != UserTicket::TYPE_USB) {
                $em->persist($ticket->setIsActive(false));
                $em->flush();
            }

            /** @var User $user */
            $user = $ticket->getUser();
            if ($user) {
                // login user
                $token = new UsernamePasswordToken(
                    $user,
                    $user->getPassword(),
                    'main',
                    $user->getRoles()
                );

                // login the user
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));

                // fire login event
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                return $this->redirectToRoute('root');
            }
        } else if ($secret) {
            $this->get('session')->getFlashBag()->add('error', 'flash.ticket.invalid_usbkey');

            $this->get('session')->set('usb_key.secret', $secret);
            return $this->redirectToRoute('nav.login_usb', ['secret' => 0, 'number' => $number, 'password' => $password]);
        }
        $secret = $this->get('session')->get('usb_key.secret', null);

        $form = $this->createForm(TicketLoginType::class, new UserTicket(), [
            'phase' => 'ticket',
            'type' => 'usb',
            'usb' => $secret,
            'school_number' => $number,
            'school_password' => $password
            ]);
        $form->handleRequest($request);

        // XXX TODO

        return $this->render('security/login/login.html.twig', [
            'error' => $error,
            'types' => $this->loginTypes,
            'type' => 'usb',
            'form' => $form->createView(),
            'ticket' => $ticket
        ]);
    }

    /**
     * Controller for all ticket logins.
     *
     * @Route("/{_locale}/login/ticket/{type}/{secret}", name="nav.login_ticket")
     * @param string $type the type of ticket to use (username|phone)
     * @param string $secret optional secret key to log in the user with (used in links emailed to user)
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function loginTicketAction($type, $secret = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $error = null;
        $user = null;
        $ticket = null;

        /** @var UserTicket $ticket */
        if ($secret && ($ticket = $em->getRepository('App:Security\UserTicket')->findOneBy([
            'ticket' => urldecode($secret), 'isActive' => true])) && $ticket->isCurrent() && $ticket->getIsActive()) {

            // login successful, invalidate ticket if not a permanent USB-key ticket
            if ($ticket->getType() != UserTicket::TYPE_USB) {
                $em->persist($ticket->setIsActive(false));
                $em->flush();
            }

            /** @var User $user */
            $user = $ticket->getUser();
            if ($user) {
                // login user
                $token = new UsernamePasswordToken(
                    $user,
                    $user->getPassword(),
                    'main',
                    $user->getRoles()
                );

                // login the user
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));

                // fire login event
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                return $this->redirectToRoute('root');
            }
        } else if ($secret){
            $this->get('session')->getFlashBag()->add('error', 'flash.ticket.invalid_ticket');

            return $this->redirectToRoute('nav.login_ticket', ['type' => $type]);
        }

        // create initial form to get the user's email or phone
        $form = $this->createForm(TicketLoginType::class, new UserTicket(), ['phase' => 'ticket', 'type' => $type]);
        $form->handleRequest($request);

        $data = $form->get($type)->getData();
        $phase = $form->get('phase')->getData();

        // Create a ticket if a user was found
        if ($data && $phase == 'ticket') {
            /** @var User $user */
            $user = $em->getRepository('App:Security\User')->findOneBy([$type => $data, 'isActive' => true]);

            if ($user) {
                $ticket_type = ($type == 'username' ? UserTicket::TYPE_EMAIL : UserTicket::TYPE_SMS);

                // Remove all active tickets before we create a new one.
                $tickets = $em->getRepository('App:Security\UserTicket')->findBy([
                    'user' => $user,
                    'type' => $ticket_type,
                    'isActive' => true,
                    'for' => UserTicket::FOR_LOGIN,
                ]);
                /** @var UserTicket $ticket */
                foreach ($tickets as $ticket) {
                    $em->merge($ticket->setIsActive(false));
                    $em->flush();
                }

                // Create new ticket
                /** @var PasswordEncoderInterface $encoder */
                $encoder = $this->get('security.password_encoder');
                $ticket = UserTicket::createFor($user, $ticket_type, $encoder);
                $em->persist($ticket);
                $em->flush();
                $trans = $this->get('translator');
                // Send ticket to user
                if ($type == 'username') {
                    // send email
                    $message = (new \Swift_Message())
                        ->setSubject($trans->trans('login.w_email', [], 'security'))
                        ->setFrom($this->getParameter('mailer_sender'))
                        ->setTo($user->getUsername())
                        ->setBody(
                            $this->renderView(
                            // App/Resources/views/email/verification/verify.html.twig
                                'email/ticket/login.html.twig',
                                [
                                    'ticket' => $ticket,
                                    'type' => $type
                                ]
                            ),
                            'text/html'
                        );
                    $this->get('mailer')->send($message);
                } else {
                    // send sms
                    $message =
                        $trans->trans('ticket.login_with_key', [
                            '%name%' => $user->getFullname(),
                            '%key%' => $ticket->getPlaintextTicket()
                        ], 'security');

                    $this->get('sms')->setTo('+358505637254')->setMessage($message)->send();
                }

                // Update the form
                $form = $this->createForm(TicketLoginType::class, $ticket, ['phase' => 'login', 'type' => $type, $type => $data]);
            } else {
                // user not found, not creating a ticket!
                $error = new CustomUserMessageAuthenticationException('ticket.invalid_' . $type);
            }
        // user was found and ticket created, now check it
        } else if ($data && $phase == 'login') {
            /** @var User $user */
            $user = $em->getRepository('App:Security\User')->findOneBy([$type => $data, 'isActive' => true]);

            if ($user) {
                $ticket_type = ($type == 'username' ? UserTicket::TYPE_EMAIL : UserTicket::TYPE_SMS);
                /** @var UserTicket $ticket */
                $ticket = $em->getRepository('App:Security\UserTicket')->findOneBy([
                    'user' => $user,
                    'type' => $ticket_type,
                    'isActive' => true,
                    'for' => UserTicket::FOR_LOGIN,
                ]);

                $form = $this->createForm(TicketLoginType::class, $ticket, ['phase' => 'login', 'type' => $type, $type => $data]);
                $form->handleRequest($request);

                /** @var SubmitButton $submitBtn */
                $submitBtn = $form->get('submit');
                /** @var SubmitButton $resendBtn */
                $resendBtn = $form->get('resend');
                if ($form->isSubmitted() && $form->isValid() && $submitBtn->isClicked() &&
                    $ticket && $ticket->isCurrent() &&
                    $this->get('security.password_encoder')->isPasswordValid($ticket, $form->get('password')->getData())) {

                    // login successful, invalidate ticket if not a permanent USB-key ticket
                    if ($ticket->getType() != UserTicket::TYPE_USB) {
                        $em->persist($ticket->setIsActive(false));
                        $em->flush();
                    }

                    // login user
                    $token =new UsernamePasswordToken(
                        $user,
                        $user->getPassword(),
                        'main',
                        $user->getRoles()
                    );

                    // login the user
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));

                    // fire login event
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                    return $this->redirectToRoute('root');
                } else if ($form->isSubmitted() && $form->isValid() && $resendBtn->isClicked()) {
                    // refresh ticket and send a new code

                    /** @var PasswordEncoderInterface $encoder */
                    $encoder = $this->get('security.password_encoder');
                    $ticket->refresh($encoder);
                    $em->merge($ticket);
                    $em->flush();

                    // Update the form
                    $form = $this->createForm(TicketLoginType::class, $ticket, ['phase' => 'login', 'type' => $type, $type => $data]);

                    // Send ticket to user
                    $trans = $this->get('translator');
                    if ($type == 'username') {
                        // send email
                        $message = (new \Swift_Message())
                            ->setSubject($trans->trans('login.w_email', [], 'security'))
                            ->setFrom($this->getParameter('mailer_sender'))
                            ->setTo($user->getUsername())
                            ->setBody(
                                $this->renderView(
                                // App/Resources/views/email/verification/verify.html.twig
                                    'email/ticket/login.html.twig',
                                    [
                                        'ticket' => $ticket,
                                        'type' => $type
                                    ]
                                ),
                                'text/html'
                            )
                        ;
                        $this->get('mailer')->send($message);
                    } else {
                        // send sms
                        $message =
                            $trans->trans('ticket.login_with_key', [], 'security') . ': ' . $ticket->getPlaintextTicket();

                        $this->get('sms')->setTo('+358505637254')->setMessage($message)->send();
                    }

                } else if ($form->isSubmitted() && $form->isValid() && $submitBtn->isClicked() &&
                    $ticket && $ticket->isCurrent()) {

                    // password of ticket did not match
                    $error = new CustomUserMessageAuthenticationException('Invalid credentials.');

                    // decrease tries left
                    $em->persist($ticket->failedTry($request->getClientIp()));
                    $em->flush();

                    // check if ticket is still valid
                    if (!$ticket->getIsActive()) {
                        // too many tries
                        // redirect to start with error
                        $this->get('session')->getFlashBag()->add('error', 'flash.ticket.too_many_tries');
                        return $this->redirectToRoute('nav.login_ticket', ['type' => $type]);
                    }
                } else if ($ticket) {
                    // password has expired, invalidate ticket
                    $em->persist($ticket->setIsActive(false));
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('error', 'flash.ticket.expired');
                    return $this->redirectToRoute('nav.login_ticket', ['type' => $type]);
                } else {
                    // ticket was not found
                    $error = new CustomUserMessageAuthenticationException('ticket.not_found');
                }
            } else {
                // user not found, not creating a ticket!
                $error = new CustomUserMessageAuthenticationException('ticket.invalid_' . $type);
            }
        }
        return $this->render('security/login/login.html.twig', [
            'error' => $error,
            'types' => $this->loginTypes,
            'type' => $type,
            'form' => $form->createView(),
            'ticket' => $ticket
        ]);
    }

    /**
     * Controller for traditional logins.
     *
     * @Route("/{_locale}/login", name="nav.login")
     * @param $request Request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        /** @var BadCredentialsException $error */
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $username = $authenticationUtils->getLastUsername();

        // $em = $this->getDoctrine()->getManager();
        // $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        //$user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));
        //$encoder = $this->container->get('security.password_encoder');
        //print_r($encoder->encodePassword($user, '18971991'));

        // log error if user is found
        if ($error) {
            $em = $this->getDoctrine()->getManager();
            if ($user = $em->getRepository(User::class)->findOneBy(['username' => $username])) {
                $log = new UserLogEvent();
                $log->setType(UserLogEvent::TYPE_LOGIN)->setLevel(UserLogEvent::LEVEL_WARNING)
                    ->setUser($user)->setTimestamp(new \DateTime('now'))
                    ->setRemoteHost($request->getClientIp())->setResult($error->getMessageKey());
                $em->persist($log);
                $em->flush();
            }
        }

        return $this->render('security/login/login.html.twig', [
            'username' => $username,
            'error' => $error,
            'types' => $this->loginTypes,
            'type' => 'traditional',
        ]);
    }

    /**
     * Controller for logout.
     *
     * @Route("/{_locale}/logout", name="logout")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction() {
        return $this->redirectToRoute('homepage', []);
    }

    /**
     * Controller for bootstrap (admin only).
     *
     * @Route("/{_locale}/bootstrap", name="bootstrap")
     * @return mixed
     */
    public function bootstrapAction()
    {
        return $this->render('security/bootstrap/bootstrap.html.twig', []);
    }

}
