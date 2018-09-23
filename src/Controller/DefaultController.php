<?php

namespace App\Controller;

use App\Controller\Interfaces\ModalEventController;
use App\Controller\Security\AdminUserController;
use App\Controller\Security\SecurityController;
use App\Entity\Schools\SchoolUnit;
use App\Entity\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use App\Entity\Security\Group;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends Controller implements ModalEventController
{
    /**
     * Something was not found.
     *
     * @Route("/{_locale}/manager/notfound/{class}/{title}/{text}", name="nav.entity_not_found")
     * @param string $class
     * @param string $title
     * @param string $text
     * @return mixed
     */
    public function notfoundAction($class, $title, $text)
    {
        return $this->render(
            'error/error.html.twig',
            array('class' => $class, 'title' => $title, 'text' => $text)
        );
    }

    /**
     * Controller for json queries. TBW
     *
     * @Route("/json/{entity}/{id}", name="json")
     * @param $entity
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jsonAction($entity, $id)
    {
        $options = [];
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('App:' . $entity);
        $entity = $repo->find($id);
        if ($entity) {
            $options['entity'] = $entity->__toJSON();
        }
        return $this->render('json/entity.json.twig', $options);
    }

    /**
     * Controller for home page. TBW
     *
     * @Route("/{_locale}/", name="homepage", requirements={"_locale"="(sv|fi|en)"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homeAction(Request $request)
    {

        /*
        $user = $this->getUser();//$this->get('security.token_storage')->getToken()->getUser();
        if ($user) {
            $request->setLocale($user->getLocale());
            $role = $user->getRoles()[0];
            if ($route = $role->getLoginRoute()) {
                return $this->redirect($user->getLocale() . $route);
            }
        } else {
            $locale = $request->getSession()->get('_locale', 'sv');
            return $this->redirect($locale);
        }
        */

        $cookie = $request->cookies->get(AdminUserController::ADMIN_SESSION_COOKIE);
        // $session = $request->getSession();
        if ($cookie && $this->getUser() === null) {
            $request->getSession()->invalidate();

            $oldSession = unserialize(file_get_contents($cookie));

            $referer = $oldSession[AdminUserController::ADMIN_URL_REFERER];
            foreach ($oldSession as $key => $val)
                $request->getSession()->set($key, $val);

            /** @var UsernamePasswordToken $token */
            $token = unserialize($oldSession['_security_main']);

            $id = $token->getUser()->getId();

            $em = $this->getDoctrine()->getManager();
            /** @var User $user */
            $user = $em->getRepository(User::class)->find($id);

            $token = new UsernamePasswordToken(
                $user,
                $user->getPassword(),
                'main',
                $user->getRoles()
            );
            // login the user
            $this->get('security.token_storage')->setToken($token);

            // fire login event
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            setcookie(AdminUserController::ADMIN_SESSION_COOKIE, '', -3600, '/');

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.user.welcome_back',
                    'parameters' => ['%name%' => $user->getFullname()]
                ]);
            if ($referer)
                return $this->redirect($referer);
            return $this->redirect('/');
        }

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * Controller that functions as a landing page.
     *
     * @Route("/", name="root")
     * @param Request $request
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        //print_r($user ? $user->getId() : 'anon');
        //return new Response('here');

        if ($user) {
            $request->setLocale($user->getLocale());
            $route = '';
            if (count($user->getGroups()) > 0) {
                /** @var Group $group */
                $group = $user->getGroups()->first();
                $route = $group->getLoginRoute();
            }
            $locale = $user->getLocale() ? $user->getLocale() : 'sv';
            return $this->redirect('/' . $locale . $route);
        }
        $locale = $request->getSession()->get('_locale', 'sv');
        return $this->redirect('/' . ($locale ? $locale : 'sv'));
    }

    /**
     * Controller that chooses the locale.
     *
     * @Route("/{_locale}/locale/{locale}/", name="locale_change")
     * @param $_locale
     * @param $locale
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function setLocaleAction($_locale, $locale, Request $request)
    {
        $request->setLocale($locale);
        $referer = str_replace('/' . $_locale . '/' , '/' . $locale . '/', $request->headers->get('referer'));

        $user = $this->getUser();//$this->get('security.token_storage')->getToken()->getUser();
        if ($user) {
            $user->setLocale($locale);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        $request->getSession()->set('_locale', $locale);
        $this->get('session')->getFlashBag()->add('info', 'flash.locale.changed');
        return $this->redirect($referer);
    }

    /**
     * Controller for managers.
     *
     * @Route("/{_locale}/manager", name="nav.manager_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerAction(Request $request)
    {
        $options = [];

        $session = $this->get('session');

        /*if ($s instanceof Session) {
            $session = new SessionEntities($s,  $this->getDoctrine());

            if ($this->getUser() !== null)
                $schools = $this->getUser()->getSchools()->toArray();
            else
                $schools = [];

            usort($schools, function (School $a, School $b) { return strcasecmp($a->getName(), $b->getName()); });

            $school_form = $this->createForm(SchoolSelectionType::class, $session, ['schools' => $schools]);
            $school_form->handleRequest($request);
            $options['school_form'] = $school_form->createView();

            if ($session->_school) {
                $schoolUnits = $session->_school->getSchoolUnits()->toArray();
                usort($schoolUnits, function (SchoolUnit $a, SchoolUnit $b) {
                    return ($a->getType()->getOrder() - $b->getType()->getOrder());
                });

                $school_unit_form = $this->createForm(SchoolUnitSelectionType::class, $session, ['schoolUnits' => $schoolUnits]);
                $school_unit_form->handleRequest($request);

                $options['school_unit_form'] = $school_unit_form->createView();
                $options['school_types'] = count($schoolUnits);
            }
        }
        */
        return $this->render('manager/index.html.twig', $options);
    }

    /**
     * Controller for system admins.
     *
     * @Route("/{_locale}/admin", name="nav.admin_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminAction(Request $request)
    {
        $options = [];
        /*$em = $this->getDoctrine()->getManager();
        $s = $this->get('session');
        if ($s instanceof Session) {
            $session = new SessionEntities($s,  $this->getDoctrine());

            $schools = $em->getRepository('App:Schools\School')->findAll();
            usort($schools, function (School $a, School $b) { return strcasecmp($a->getName(), $b->getName()); });

            $school_form = $this->createForm(SchoolSelectionType::class, $session, ['schools' => $schools]);
            $school_form->handleRequest($request);
            $options['school_form'] = $school_form->createView();

            if ($session->_school) {
                // ** @var ArrayCollection $schoolUnits
                $schoolUnits = $session->_school->getSchoolUnits();

                $school_unit_form = $this->createForm(SchoolUnitSelectionType::class, $session, ['schoolUnits' => $schoolUnits]);
                $school_unit_form->handleRequest($request);

                $options['school_unit_form'] = $school_unit_form->createView();
                $options['school_types'] = $schoolUnits->count();
            }
        }*/

        //$em = $this->getDoctrine()->getManager();
        /** @var SchoolUnit $su */
        //$su = $em->getRepository(SchoolUnit::class)->find(663);
        //$sun = $su->createName('Test', new \DateTime('now'));

//        $sn->setAbbreviation('' . time());
//        $sn->setName('Albert Edelfelts skola / ' . time());
        //$em->merge($sun);
        //$em->flush();


        return $this->render('admin/index.html.twig', $options);
    }
}
