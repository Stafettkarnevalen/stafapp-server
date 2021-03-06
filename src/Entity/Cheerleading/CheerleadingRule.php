<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 14.06
 */
namespace App\Entity\Cheerleading;

use App\Entity\Documentation\Documentation;
use App\Entity\Events\CheerleadingHasRule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CheerleadingRuleRepository")
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Cheerleading
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class CheerleadingRule extends Documentation
{

    /**
     * @ORM\OneToMany(targetEntity="CheerleadingHasRule", mappedBy="rule", cascade={"persist", "merge", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     * @var ArrayCollection $hasEvents The events that this rule affects
     */
    protected $hasEvents;

    /**
     * EventRule constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->hasEvents = new ArrayCollection();
    }

    /**
     * Gets the hasEvents.
     *
     * @return ArrayCollection
     */
    public function getHasEvents()
    {
        return $this->hasEvents;
    }

    /**
     * Sets the hasEvents.
     *
     * @param ArrayCollection $hasEvents
     * @return $this
     */
    public function setHasEvents($hasEvents)
    {
        $this->hasEvents = $hasEvents;

        return $this;
    }

    /**
     * Gets the Events.
     *
     * @return ArrayCollection
     */
    public function getEvents()
    {
        $events = new ArrayCollection();
        foreach ($this->hasEvents as $eventHasRule)
            $events->add($eventHasRule->getEvent());
        return $events;
    }


    /**
     * Add a event to this rule.
     *
     * @param CheerleadingEvent $event
     * @return $this
     */
    public function addEvent(CheerleadingEvent $event)
    {
        if ($this->hasEvent($event)) {
            return $this;
        }
        $hasEvent = new CheerleadingHasRule();
        $hasEvent->setEvent($event)->setRule($this)->setOrder($event->getHasRules()->count());
        return $this;
    }

    /**
     * Removes a event from this rule.
     *
     * @param CheerleadingEvent $event
     * @return $this
     */
    public function removeEvent(CheerleadingEvent $event)
    {
        if (!$this->hasEvent($event)) {
            return $this;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('event', $event))
            ->andWhere(Criteria::expr()->neq('rule', $this))
        ;

        $this->hasEvents = $this->hasEvents->matching($criteria);

        return $this;
    }

    /**
     * Checks if event has a rule
     *
     * @param CheerleadingEvent $event The event to check for
     *
     * @return bool
     */
    public function hasEvent(CheerleadingEvent $event)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('event', $event))
            ->andWhere(Criteria::expr()->eq('rule', $this))
        ;

        return ($this->hasEvents->matching($criteria)->count() == 1);
    }

}