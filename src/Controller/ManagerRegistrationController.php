<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/11/2016
 * Time: 21.21
 */
// src/App/Controller/ManagerRegistrationController.php
namespace App\Controller;

use App\Controller\Interfaces\ModalEventController;
use App\Entity\Schools\School;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use App\Form\User\UserRegistrationType;
use App\Entity\Security\User;
use App\Entity\Security\Group;
use App\Form\User\UserVerificationType;

class ManagerRegistrationController extends Controller implements ModalEventController
{
    /**
     * Asserts that the Group of the registered user exists.
     *
     * @return Group|object
     */
    public function assertRole() {
        $em = $this->getDoctrine()->getManager();
        $role = $em->getRepository(Group::class)->findOneBy(['role' => 'ROLE_MANAGER']);
        if (!$role) {
            $role = new Group();
            $role->setRole('lagledare@stafettkarnevalen.fi');
            $em->persist($role);
            $em->flush();
        }
        return $role;
    }

    /**
     * Default route, register a manager account.
     *
     * @Route("/{_locale}/register/manager/{token}", name="nav.register_manager")
     * @param string $token
     * @param Request $request
     *
     * @return mixed
     */
    public function registerAction($token = null, Request $request)
    {
        // 1) build the form
        $user = new User();
        $user->setLogins(0);
        $user->setIsActive(false);
        $user->addRole($this->assertRole());

        if ($token) {
            $data = json_decode(base64_decode(urldecode($token)));
            if ($data['email'])
                $user->setUsername($data['email']);
            if ($data['firstname'])
                $user->setFirstname($data['firstname']);
            if ($data['lastname'])
                $user->setLastname($data['lastname']);
            if ($data['phone'])
                $user->setPhone($data['phone']);
        }

        $form = $this->createForm(UserRegistrationType::class, $user);

        // 2) handle    the submit (will only happen on POST)
        $form->handleRequest($request);

        /*foreach($form->getErrors() as $key => $error) {
            print_r($error->getCause()->getPropertyPath());
            print_r($error->getMessageTemplate());
            print_r($error->getMessage());
        }
        */
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // 5) send email for verification
            $message = (new \Swift_Message())
                ->setSubject('Test')
                ->setFrom('robert.jurgens@idrott.fi')
                //->setTo($user->getEmail())
                // XXX Change in prod
                ->setTo('juppe.jurgens@gmail.com')
                ->setBody(
                    $this->renderView(
                        // App/Resources/views/email/verification/verify.html.twig
                        'email/verification/verify.html.twig',
                        array(
                            'field' => 'email',
                            'user' => $user
                        )
                    ),
                    'text/html'
                )
            ;
            $this->get('mailer')->send($message);

            // 6) send SMS for verification
            $trans = $this->get('translator');

            $message =
                $trans->trans('label.phonehash', [], 'user') . ': ' . $user->getPhonehash() . "\n\n" .
                $trans->trans('label.link', [], 'user') . ': ' . $request->getSchemeAndHttpHost() .
                $this->generateUrl('verify', ['id' => $user->getId(), 'field' => 'phone', 'hash' => $user->getPhonehash()]);

            $this->get('sms')
                ->setTo('+358505637254')
                ->setMessage($message)
                ->send();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('nav.register_manager_verify', ['id'=>$user->getId(), 'field'=>'email']);
        }

        return $this->render(
            'manager/registration/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Registration is done.
     *
     * @Route("/{_locale}/register/manager_done", name="nav.register_manager_done")
     * @return mixed
     */
    public function doneAction()
    {
        return $this->render(
            'manager/registration/done.html.twig',
            array('user' => $this->get('security.token_storage')->getToken()->getUser())
        );
    }

    /**
     * Block a user from being registered, in case of miss use.
     *
     * @Route("/{_locale}/register/manager_block/{id}", name="nav.register_manager_block")
     * @param integer $id
     * @return mixed
     */
    public function blockAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['id' => $id, 'isActive' => false]);
        $user->setIsBlocked(true);
        $em->persist($user);
        $em->flush();

        return $this->render(

            'manager/block/block.html.twig',
            array(
                'user' => $user,
            )
        );
    }

    /**
     * Verifies that the user registered owns the email and phone provided.
     *
     * @Route("/{_locale}/register/manager_verify/{id}/{field}/{hash}", name="nav.register_manager_verify")
     * @param integer $id
     * @param string $field
     * @param string $hash
     * @param Request $request
     * @return mixed
     */
    public function verifyAction($id, $field, $hash = null, Request $request)
    {
        $key = $field . 'hash';
        $setter = "set" . $key;
        $getter = "get" . $key;
        $em = $this->getDoctrine()->getManager();

        if ($hash) {
            /** @var User $user */
            $user = $em->getRepository(User::class)->findOneBy(['id' => $id, $key => $hash, 'isActive' => false]);
            if (!$user) {
                return $this->redirectToRoute('nav.register_manager_verify', ['id' => $id, 'field' => $field]);
            } else if (!$user->$getter()) {
                if ($field === 'email') {
                    return $this->redirectToRoute('nav.register_manager_verify', ['id' => $id, 'field' => 'phone']);
                } else {
                    // activate user
                    $user->setIsActive(true);
                    $em->persist($user);
                    $em->flush();

                    // create a login token for programmatic login
                    $token = new UsernamePasswordToken(
                        $user,
                        $user->getPassword(),
                        'dev',
                        $user->getRoles()
                    );

                    // login the user
                    $this->get('security.token_storage')->setToken($token);

                    // fire login event
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                    return $this->redirectToRoute('nav.register_manager_done');
                }
            } else {
                $user->$setter(null);
                $em->persist($user);
                $em->flush();
                return $this->redirectToRoute('nav.register_manager_verify', ['id' => $id, 'field' => $field]);
            }
        } else {
            /** @var User $user */
            $user = $em->getRepository(User::class)->findOneBy(['id' => $id, 'isActive' => false]);

            if (!$user) {
                return $this->redirectToRoute('nav.entity_not_found', [
                    'class' => 'User',
                    'title' => 'title.verifiable_manager_not_found',
                    'text' => 'text.verifiable_manager_not_found'
                ]);
            } else if (!$user->$getter()) {
                if ($field === 'email') {
                    return $this->redirectToRoute('nav.register_manager_verify', ['id' => $id, 'field' => 'phone']);
                } else {
                    if ($user->getEmailhash() !== null) {
                        return $this->redirectToRoute('nav.register_manager_verify', ['id' => $id, 'field' => 'email']);
                    }
                    // activate user
                    $user->setIsActive(true);
                    $em->persist($user);
                    $em->flush();

                    // create a login token for programmatic login
                    $token = new UsernamePasswordToken(
                        $user,
                        $user->getPassword(),
                        'dev',
                        $user->getRoles()
                    );

                    // login the user
                    $this->get('security.token_storage')->setToken($token);

                    // fire login event
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                    return $this->redirectToRoute('nav.register_manager_done');
                }
            }
            $target = $field == 'email' ? 'username' : $field;
            $form = $this->createForm(UserVerificationType::class, $user, ['fieldname' => $target, 'hashname' => $key]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->get('submit')->isClicked() && $form->isValid()) {
                $user->$setter(null);
                $em->persist($user);
                $em->flush();
                if ($field === 'email') {
                    return $this->redirectToRoute('nav.register_manager_verify', ['id' => $id, 'field' => 'phone']);
                } else {
                    if ($user->getEmailhash() !== null) {
                        return $this->redirectToRoute('nav.register_manager_verify', ['id' => $id, 'field' => 'email']);
                    }
                    // activate user
                    $user->setIsActive(true);
                    $em->persist($user);
                    $em->flush();

                    // create a login token for programmatic login
                    $token =new UsernamePasswordToken(
                        $user,
                        $user->getPassword(),
                        'dev',
                        $user->getRoles()
                    );

                    // login the user
                    $this->get('security.token_storage')->setToken($token);

                    // fire login event
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                    return $this->redirectToRoute('nav.register_manager_done');
                }
            } else if ($form->isSubmitted() && $form->get('resend')->isClicked()) {
                if ($key == 'phonehash') {
                    $trans = $this->get('translator');

                    $message =
                        $trans->trans('label.phonehash', [], 'user') . ': ' . $user->getPhonehash() . "\n\n" .
                        $trans->trans('label.link', [], 'user') . ': ' . $request->getSchemeAndHttpHost() .
                        $this->generateUrl('verify', ['id' => $user->getId(), 'field' => 'phone', 'hash' => $user->getPhonehash()]);

                    $this->get('sms')
                        ->setTo('+358505637254')
                        ->setMessage($message)
                        ->send();

                    $this->addFlash('message_sent', $key . '.sent');

                } else if ($key == 'emailhash') {
                    $message = (new \Swift_Message())
                        ->setSubject('Test')
                        ->setFrom('robert.jurgens@idrott.fi')
                        //->setTo($user->getEmail())
                        ->setTo('juppe.jurgens@gmail.com')
                        ->setBody(
                            $this->renderView(
                            // App/Resources/views/email/verification/verify.html.twig
                                'email/verification/verify.html.twig',
                                array(
                                    'field' => $field,
                                    'user' => $user
                                )
                            ),
                            'text/html'
                        )
                    ;
                    $this->get('mailer')->send($message);

                    $this->addFlash('message_sent', $key . '.sent');
                }
            }
            return $this->render(
                'manager/verification/verify.html.twig',
                array(
                    'form' => $form->createView(),
                    'field' => $field,
                    'user' => $user,
                )
            );
        }
    }

    /**
     * Adds a manager to a school.
     *
     * @Route("/{_locale}/manager/add_to_school/{mgr_id}/{sch_nbr}/{sch_pwd}", name="nav.add_manager_to_school")
     * @param integer $mgr_id
     * @param integer $sch_nbr
     * @param string $sch_pwd
     * @param Request $request
     * @return mixed
     */
    public function addToSchoolAction($mgr_id = null, $sch_nbr = null, $sch_pwd = null, Request $request)
    {
        $options = [];
        $manager = null;
        $school = null;
        $em = $this->getDoctrine()->getManager();
        $em->clear();
        $referer = $this->get('session')->get('_referer');
        if ($referer === null) {
            $referer = $request->headers->get('referer');
            $this->get('session')->set('_referer', $referer);
        }

        if ($mgr_id !== null) {
            /** @var User $manager */
            $manager = $em->getRepository(User::class)->find($mgr_id);
            if ($manager === null) {
                $this->get('session')->set('_referer', null);
                return $this->redirectToRoute('nav.entity_not_found', [
                    'class' => 'User',
                    'title' => 'title.user_with_id_not_found',
                    'text' => 'text.user_with_id_not_found'
                ]);
            }
        } else {
            return $this->redirectToRoute('nav.add_manager_to_school', [
                'mgr_id' => $this->getUser()->getId()
            ]);
        }
        $options['manager'] = $manager;
        $form = $this->createForm(FormType::class, null, ['translation_domain' => 'school']);

        $form->add('number', TextType::class, ['label' => 'label.number', 'attr' => ['pattern' => '\\d{5}']]);
        $form->add('submit', SubmitType::class, array('translation_domain' => 'messages', 'right_icon' => 'fa-chevron-right', 'attr' => ['class' => 'btn-primary'], 'label' => 'next'));

        if ($sch_nbr !== null) {
            /** @var School $school */
            $school = $em->getRepository(School::class)->findOneBy(['number' => $sch_nbr]);
            if ($school === null) {
                $this->get('session')->getFlashBag()->add('error', 'add_to_school.school_not_found');
                return $this->redirectToRoute('nav.add_manager_to_school', [
                    'mgr_id' => $mgr_id,
                ]);
            }
            if ($school->hasManager($manager)) {
                $this->get('session')->getFlashBag()->add('error', 'add_to_school.already_manages_school');
                return $this->redirectToRoute('nav.add_manager_to_school', [
                    'mgr_id' => $mgr_id,
                ]);
            }

            $form->add('number', TextType::class, [
                'label' => 'label.number',
                'attr' => [
                    'pattern' => '\\d{5}', 'readonly' => 'readonly'
                ],
                'data' => $sch_nbr
            ]);
            $form->add('password', TextType::class, ['label' => 'label.password', 'attr' => ['pattern' => '\\d{8}']]);

            $options['prev'] = $this->get('router')->generate('nav.add_manager_to_school', ['mgr_id' => $mgr_id]);

            $options['number'] = $sch_nbr;
            $options['school'] = $school;

            if ($sch_pwd !== null) {
                if ($school->getPassword() != $sch_pwd) {
                    $this->get('session')->getFlashBag()->add('error', 'add_to_school.invalid_password');
                    return $this->redirectToRoute('nav.add_manager_to_school', [
                        'mgr_id' => $mgr_id,
                        'sch_nbr' => $sch_nbr
                    ]);
                }
                $form->add('password', TextType::class, [
                    'label' => 'label.password',
                    'attr' => [
                        'pattern' => '\\d{8}', 'readonly' => 'readonly'
                    ],
                    'data' => $sch_pwd
                ]);
                $form->add('ready', HiddenType::class, array('attr' => ['value' => 1]));
                $form->add('submit', SubmitType::class, array('right_icon' => 'fa-check', 'attr' => ['class' => 'btn-primary'], 'label' => 'finish'));
                $options['password'] = $sch_pwd;
                $options['prev'] = $this->get('router')->generate('nav.add_manager_to_school', ['mgr_id' => $mgr_id, 'sch_nbr' => $sch_nbr]);

            }
        }

        $translator = $this->get('translator');
        $form->handleRequest($request);

        $data = $form->getData();
        if (isset($data['ready']) && $data['ready']) {
            $school->addManager($manager);
            $em->persist($school);
            $em->flush();

            $referer = $this->get('session')->get('_referer');

            $this->get('session')->set('_referer', null);

            $this->get('session')->set('_school', $school);
            $this->get('session')->getFlashBag()->add('success', 'add_to_school.added');

            return $this->redirect($referer);
        } else if (isset($data['number']) && $data['number'] && isset($data['password']) && $data['password'] && !$sch_pwd) {
            return $this->redirectToRoute('nav.add_manager_to_school', [
                'mgr_id' => $mgr_id,
                'sch_nbr' => $data['number'],
                'sch_pwd' => $data['password']
            ]);
        } else if ($data['number'] && $data['number'] && !$sch_nbr) {
            return $this->redirectToRoute('nav.add_manager_to_school', [
                'mgr_id' => $mgr_id,
                'sch_nbr' => $data['number']
            ]);
        }

        $options['route_trans_vars'] = [
            '%name%' => $manager->getFullname(),
            '%school%' => $school ? $school->getName() : $translator->trans('label.a_school')
        ];

        $options['form'] = $form->createView();

        return $this->render('manager/add_to_school/index.html.twig', $options);
    }

}