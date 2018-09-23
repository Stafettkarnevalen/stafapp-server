<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 17.35
 */
namespace App\Entity\Events;

use App\Entity\Schedule\ScheduledEntity;
use App\Entity\Traits\ApplyDateIntervalTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use App\Entity\Traits\NotesTrait;
use App\Entity\Traits\VersionedPriceTrait;
use App\Entity\Services\Service;
use App\Entity\Interfaces\ScheduledEvent;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="competition_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @UniqueEntity(fields="event,service", message="event.exists")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Event
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class Competition extends ScheduledEntity
{
    /** use date interval trait */
    use ApplyDateIntervalTrait;

    /** Use price field */
    use VersionedPriceTrait;

    /** Use notes field */
    use NotesTrait;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="cup_name_fld", type="string", length=64, nullable=true)
     * @var string $cupName The name of the cup awarded to the winner of this competition
     */
    protected $cupName;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="ceremnony_fld", type="datetime", nullable=true)
     * @var \DateTime $starts The timestamp when this competition has the award ceremony
     */
    protected $ceremony;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="results_public_fld", type="datetime", nullable=true)
     * @var \DateTime $resultsGoPublic The timestamp when the results can be published
     */
    protected $resultsGoPublic;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="competitions")
     * @ORM\JoinColumn(name="event_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Event $event The event that this competition is based on
     */
    protected $event;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Services\Service", inversedBy="eventCompetitions")
     * @ORM\JoinColumn(name="service_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Service $service The service type that provides this competition
     */
    protected $service;

    /**
     * @ORM\OneToMany(targetEntity="EventSquad", mappedBy="competition", cascade={"persist", "remove"})
     * @var ArrayCollection $squads The squads taking part in this competition
     */
    protected $squads;


    /**
     * Competition constructor.
     */
    public function __construct()
    {
        $this->squads = new ArrayCollection();
    }

    /**
     * Gets the cup name.
     *
     * @return string|null
     */
    public function getCupName()
    {
        return $this->cupName;
    }

    /**
     * Sets the cup name.
     *
     * @param string|null $cupName
     * @return $this
     */
    public function setCupName($cupName)
    {
        $this->cupName = $cupName;

        return $this;
    }

    /**
     * Gets the Event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Sets the Event.
     *
     * @param Event $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Gets the Service.
     *
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Sets the Service
     *
     * @param Service $service
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Gets the season.
     *
     * @return integer
     */
    public function getSeason()
    {
        return $this->getService()->getSeason();
    }

    /**
     * Gets the squads.
     *
     * @return ArrayCollection
     */
    public function getSquads()
    {
        return $this->squads;
    }

    /**
     * Sets the Squads.
     *
     * @param ArrayCollection $squads
     * @return $this
     */
    public function setSquads($squads)
    {
        $this->squads = $squads;

        return $this;
    }

    /**
     * Gets the winning squad.
     *
     * @return EventSquad|null
     */
    public function getWinnerSquad()
    {
        foreach($this->getSquads() as $squad) {
            if ($squad->getRank() == 1)
                return $squad;
        }
        return null;
    }

    /**
     * Sets the winning squad.
     *
     * @param EventSquad $squad
     * @return $this
     */
    public function setWinnerSquad($squad)
    {
        $squad->setRank(1);

        return $this;
    }


    /**
     * Gets the runner up squads.
     *
     * @return ArrayCollection
     */
    public function getRunnerUpSquads()
    {
        $runnersUp = new ArrayCollection();
        foreach($this->getSquads() as $squad) {
            if ($squad->getRank() == 2)
                $runnersUp->add($squad);
        }
        return $runnersUp;
    }

    /**
     * Sets the runner up squads.
     *
     * @param ArrayCollection $squads
     * @return $this
     */
    public function setRunnerUpSquads(ArrayCollection $squads)
    {
        foreach($squads as $squad) {
            $squad->setRank(2);
        }

        return $this;
    }

    /**
     * Gets a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getEvent()->__toString();
    }

    /**
     * Gets the ceremony.
     *
     * @return \DateTime
     */
    public function getCeremony()
    {
        return $this->ceremony;
    }

    /**
     * Sets the ceremony.
     *
     * @param \DateTime $ceremony
     * @return $this
     */
    public function setCeremony($ceremony)
    {
        $this->ceremony = $ceremony;

        return $this;
    }

    /**
     * Gets the resultsGoPublic.
     *
     * @return mixed
     */
    public function getResultsGoPublic()
    {
        return $this->resultsGoPublic;
    }

    /**
     * Sets the resultsGoPublic.
     *
     * @param mixed $resultsGoPublic
     * @return $this
     */
    public function setResultsGoPublic($resultsGoPublic)
    {
        $this->resultsGoPublic = $resultsGoPublic;

        return $this;
    }


    /**
     * Gets the fields that can be modified with a \DateInterval.
     *
     * @return array
     */
    public function getDateIntervalApplicableFields()
    {
        return [$this->getStarts(), $this->getCeremony(), $this->getResultsGoPublic()];
    }

    /**
     * Gets all ScheduledEvents of this entity.
     *
     * @return ArrayCollection
     */
    public function getSchedule()
    {
        $events = new ArrayCollection();
        $events[$this->getStarts()->format('c')] = new EventSchedule($this, ScheduledEvent::EVENT_TYPE_STARTS);
        $events[$this->getCeremony()->format('c')] = new EventSchedule($this, ScheduledEvent::EVENT_TYPE_CEREMONY);
        return $events;
    }
}