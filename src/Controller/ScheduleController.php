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
use App\Entity\Schedule\ScheduledEntity;
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

class ScheduleController extends Controller implements ModalEventController
{
    /**
     * Controller for handling yearly schedule
     *
     * @Route("/{_locale}/schedules/schedule/{year}/{heats}", name="nav.schedule")
     * @param integer $year
     * @param Request $request
     * @return mixed
     */
    public function scheduleAction($year = null, $heats = null, Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        if ($year === null)
            $year = $session->get('schedule_year', date('Y'));
        if ($heats === null)
            $heats = $session->get('schedule_heats', false);

        $session->set('schedule_year', $year);
        $session->set('schedule_heats', $heats);

        $years = array_reverse(range(1961, date('Y') + 1));

        $qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder();
        $qry = $qb->select(['se'])
            ->from(ScheduledEntity::class, 'se')
            ->where('se.starts >= :from AND se.starts <= :until' .
                ($heats ? '' : ' AND se NOT INSTANCE OF App\\Entity\\Relays\\Heat'))
            ->setParameter('from', new \DateTime($year . '-01-01 00:00:00'))
            ->setParameter('until', new \DateTime($year . '-12-31 23:59:59'))
            ->orderBy('se.starts', 'ASC')
            ->addOrderBy('se.id', 'ASC')
            ->getQuery();

        $schedule = $qry->getResult();

        return $this->render('schedules/schedule.html.twig', [
            'year' => $year,
            'years' => $years,
            'schedule' => new ArrayCollection($schedule),
        ]);
    }
}