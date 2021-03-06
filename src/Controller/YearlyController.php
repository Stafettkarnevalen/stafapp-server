<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 07/09/2017
 * Time: 8.39
 */

namespace App\Controller;


use App\Controller\Interfaces\ModalEventController;
use App\Entity\Relays\Heat;
use App\Entity\Relays\Race;
use App\Entity\Relays\Relay;
use App\Entity\Relays\Round;
use App\Entity\Services\ServiceCategory;
use App\Entity\Services\ServiceType;
use App\Form\Relay\RaceSelectionType;
use App\Repository\RelayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\Translator;

class YearlyController extends Controller implements ModalEventController
{
    /**
     * @var array yearly sections provided by the service.
     */
    private $yearlySections = [
        'race' => ['trophy', 'yearly.relays', 'yearly.info_relays', 'nav.yearly_relays', 'admin/yearly/relays_form.html.twig'],
        'cheerleading' => ['bullhorn', 'yearly.cheerleading', 'yearly.info_cheerleading', 'nav.yearly_cheerleading', 'admin/yearly/cheerleading_form.html.twig'],
        'mascot' => ['github-alt', 'yearly.mascot', 'yearly.info_mascot', 'nav.yearly_mascot', 'admin/yearly/mascot_form.html.twig'],
        'services' => ['cart-plus', 'yearly.services', 'yearly.info_services', 'nav.yearly_services', 'admin/yearly/services_form.html.twig']
    ];

    /**
     * Asserts that a ServiceType for the Races exists.
     *
     * @param integer $year
     * @param ServiceCategory $type
     * @return ServiceType
     */
    private function assertServiceType($year, $category)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var ServiceType $serviceType */
        $serviceType = $em->getRepository(ServiceType::class)->findOneBy([
            'season' => $year,
            'serviceCategory' => $category
        ]);
        if (!$serviceType) {
            $serviceType = new ServiceType();
            /** @var Translator $trans */
            $serviceType->setServiceCategory($category)->setSeason($year);
            $em->persist($serviceType);
            $em->flush();
        }
        return $serviceType;
    }

    /**
     * Copies all the races from one year to another.
     *
     * @Route("/{_locale}/admin/yearly/copy/{year}", name="nav.admin_yearly_copy")
     * @param integer $year The target year
     * @param Request $request
     * @return mixed
     */
    public function copyYearAction($year, $type = 'race', Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serviceType = $this->assertServiceType($year, $type);
        $form = null;

        // only copy to a year with no predefined races
        if ($serviceType->getEventsForType()->count() == 0) {
            $serviceTypes = $em->getRepository(ServiceType::class)->findBy(['type' => $type], ['season' => 'DESC']);
            if (($i = array_search($serviceType, $serviceTypes)) !== false)
                unset($serviceTypes[$i]);

            $fb = $this->createFormBuilder([], [
                'translation_domain' => 'yearly',
                'attr' => ['action' => $request->getPathInfo()],
            ]);
            $fb
                ->add('serviceType', ChoiceType::class, [
                    'choices' => $serviceTypes,
                    'choice_label' => 'season',
                    'expanded' => false,
                    'multiple' => false,
                    'label' => 'label.choose_year',
                    'mapped' => false,
                ])
                ->add('timestamp', DateTimeType::class, [
                    'data' => new \DateTime('now'),
                    'label' => 'label.timestamp'
                ])
                ->add('close', ButtonType::class, [
                    'left_icon' => 'fa-chevron-left',
                    'right_icon' => 'fa-close',
                    'label' => 'label.close',
                    'attr' => [
                        'class' => 'btn-default',
                        'data-dismiss' => 'modal',
                    ],
                ])
                ->add('submit', SubmitType::class, [
                    'left_icon' => 'fa-copy',
                    'right_icon' => 'fa-check',
                    'attr' => ['class' => 'btn-success'],
                    'label' => 'label.copy'
                ])
            ;

            $form = $fb->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                /** @var ServiceType $serviceType */
                $serviceType = $form->get('serviceType')->getData();
                $events = array_merge($serviceType->getEventsForType()->toArray());

                if ($type == 'race') {
                    usort($events, function(Race $a, Race $b) {
                        if (!$a->getRounds()->get(0) && $b->getRounds()->get(0))
                            return -1;
                        else if ($a->getRounds()->get(0) && !$b->getRounds()->get(0))
                            return 1;
                        else if (!$a->getRounds()->get(0) && !$b->getRounds()->get(0))
                            return 0;
                        return ($a->getRounds()->get(0)->getStarts()->format('U') -
                            $b->getRounds()->get(0)->getStarts()->format('U'));
                    });

                    $interval = false;
                    /** @var Race $race */
                    foreach ($events as $race) {
                        if ($race->getRounds()->count() == 0)
                            continue;
                        if (!$interval) {
                            /** @var \DateTime $first */
                            $first = $race->getRounds()->get(0)->getStarts();
                            /** @var \DateTime $now */
                            $now = $form->get('timestamp')->getData();
                            $interval = $now->diff($first, true);
                        }
                        /** @var Race $cloned */
                        $cloned = $race->cloneEntity();
                        $cloned->setServiceType($serviceType);
                        $rounds = new ArrayCollection([]);
                        foreach ($race->getRounds() as $round) {
                            /** @var Round $cr */
                            $cr = $round->cloneEntity();
                            $cr->setRace($cloned);
                            $cr->applyDateInterval($interval);
                            $heats = new ArrayCollection([]);
                            /** @var Heat $heat */
                            foreach ($round->getHeats() as $heat) {
                                /** @var Heat $ch */
                                $ch = $heat->cloneEntity();
                                $ch->setRound($cr);
                                $ch->applyDateInterval($interval);
                                $heats->add($ch);
                            }
                            $cr->setHeats($heats);
                            $rounds->add($cr);
                        }
                        $cloned->setRounds($rounds);
                        $em->persist($cloned);
                        $em->flush();
                    }
                }
                $this->get('session')->getFlashBag()->add('success', 'flash.events.copied');
            }
        }
        $formView = $form ? $form->createView() : null;
        return $this->render('admin/yearly/copy.html.twig', [
            'year' => $year,
            'form' => $formView,
            'btns' => $formView ?
                [
                    $formView->offsetGet('close'),
                    $formView->offsetGet('submit'),
                ] : [

                ],
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }

    /**
     * Controller for handling the whole event (admin only).
     *
     * @Route("/{_locale}/admin/yearly/view/{year}/{section}", name="nav.admin_yearly")
     * @param integer $year
     * @param string $section
     * @param Request $request
     * @return mixed
     */
    public function summaryAction($year = null, $section = 'race', Request $request)
    {
        $action = "{$section}Action";
        return $this->$action($year, $request);
    }

    public function raceAction($year = null, Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        if ($year === null)
            $year = $session->get('admin_yearly_year', date('Y'));
        $session->set('admin_yearly_year', $year);

        $years = array_reverse(range(1961, date('Y') + 1));


        /** @var ServiceType $relayService */
        $relayService = $this->assertServiceType($year, null);

        /** @var RelayRepository $relayRepo */
        $relayRepo = $em->getRepository(Relay::class);
        $relays = $relayRepo->findActive($year, ['name' => 'ASC', 'minClassOf' => 'ASC']);

        $forms = [];
        /** @var Relay $relay */
        foreach($relays as $relay) {
            $race = $relay->getRace($relayService);
            if ($race === null) {
                $race = new Race();
                $race->setRelay($relay)->setServiceType($relayService);
            }
            /** @var FormFactory $formFactory */
            $formFactory = $this->get('form.factory');
            $form = $formFactory->createNamed('form_' . $relay->getId(),
                RaceSelectionType::class, $race);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $race = $form->getData();
                /** @var Race $race */
                if ($race->getId() !== null) {
                    if ($_POST['form_' . $relay->getId()]['active']) {
                        print_r('merge');
                        $em->merge($race);
                    } else {
                        print_r('remove');
                        $em->remove($race);

                        $race = new Race();
                        $race->setRelay($relay)->setServiceType($relayService);

                        $form->setData($race);
                    }
                } else {
                    if ($_POST['form_' . $relay->getId()]['active']) {
                        print_r('persist');
                        $em->persist($race);
                    }
                }
                $em->flush();

                // print_r($form->getData());
            } else if ($form->isSubmitted()){
                print_r($form->getErrors()->current()->getMessage());
            }
            $forms[$relay->getId()] = $form->createView();
        }

        $form = null;
        if ($relayService->getRaces()->count() == 0) {
            $serviceTypes = $em->getRepository(ServiceType::class)->findBy(['type' => 'race'], ['season' => 'DESC']);
            if (($i = array_search($relayService, $serviceTypes)) !== false)
                unset($serviceTypes[$i]);

            $fb = $this->createFormBuilder([], ['translation_domain' => 'yearly']);

            $fb
                ->add('serviceType', ChoiceType::class, [
                    'choices' => $serviceTypes,
                    'choice_label' => 'season',
                    'expanded' => false,
                    'multiple' => false,
                    'label' => 'label.choose_year',
                    'mapped' => false,
                ])
                ->add('timestamp', DateTimeType::class, [
                    'data' => new \DateTime('now'),
                    'label' => 'label.timestamp'
                ])
                ->add('submit', SubmitType::class, ['left_icon' => 'fa-copy', 'right_icon' => 'fa-check', 'attr' => ['class' => 'btn-success'], 'label' => 'label.copy'])
            ;
            $form = $fb->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                /** @var ServiceType $serviceType */
                $serviceType = $form->get('serviceType')->getData();
                $races = array_merge($serviceType->getRaces()->toArray());

                usort($races, function(Race $a, Race $b) {
                    if (!$a->getRounds()->get(0) && $b->getRounds()->get(0))
                        return -1;
                    else if ($a->getRounds()->get(0) && !$b->getRounds()->get(0))
                        return 1;
                    else if (!$a->getRounds()->get(0) && !$b->getRounds()->get(0))
                        return 0;
                    return ($a->getRounds()->get(0)->getStarts()->format('U') -
                        $b->getRounds()->get(0)->getStarts()->format('U'));
                });

                $interval = false;
                /** @var Race $race */
                foreach ($races as $race) {
                    if ($race->getRounds()->count() == 0)
                        continue;
                    if (!$interval) {
                        /** @var \DateTime $first */
                        $first = $race->getRounds()->get(0)->getStarts();
                        /** @var \DateTime $now */
                        $now = $form->get('timestamp')->getData();
                        $interval = $now->diff($first, true);
                    }
                    /** @var Race $cloned */
                    $cloned = $race->cloneEntity();
                    $cloned->setServiceType($relayService);
                    $rounds = new ArrayCollection([]);
                    foreach ($race->getRounds() as $round) {
                        /** @var Round $cr */
                        $cr = $round->cloneEntity();
                        $cr->setRace($cloned);
                        $cr->applyDateInterval($interval);
                        $heats = new ArrayCollection([]);
                        /** @var Heat $heat */
                        foreach ($round->getHeats() as $heat) {
                            /** @var Heat $ch */
                            $ch = $heat->cloneEntity();
                            $ch->setRound($cr);
                            $ch->applyDateInterval($interval);
                            $heats->add($ch);
                        }
                        $cr->setHeats($heats);
                        $rounds->add($cr);
                    }
                    $cloned->setRounds($rounds);
                    $em->persist($cloned);
                    $em->flush();
                }
                $this->get('session')->getFlashBag()->add('success', 'flash.races.copied');
            }
        }

        return $this->render('admin/yearly/yearly.html.twig', [
            'sections' => $this->yearlySections,
            'section' => 'RACE',
            'year' => $year,
            'years' => $years,
            'races' => $relayService->getRaces(),
            'relays' => $relays,
            'forms' => $forms,
            'form' => $form ? $form->createView() : null,
        ]);
    }
}