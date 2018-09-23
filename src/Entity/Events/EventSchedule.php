<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/11/2017
 * Time: 17.11
 */
namespace App\Entity\Events;

use App\Entity\Interfaces\ScheduledEvent;

/**
 * Class EventSchedule
 * @package App\Entity\Event
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class EventSchedule implements ScheduledEvent
{
    /** @var  Competition $competition The competition that is responsible for this scheduled event */
    protected $competition;

    /** @var  string $type The type of the scheduled event*/
    protected $type;

    /**
     * ScheduledEventEvent constructor.
     * @param Competition $competition
     * @param string $type
     */
    public function __construct($competition, $type)
    {
        $this->competition = $competition;
        $this->type = $type;
    }

    /**
     * Gets the start time.
     *
     * @return mixed|null
     */
    public function getStarts()
    {
        switch ($this->type) {
            case ScheduledEvent::EVENT_TYPE_STARTS:
                return $this->competition->getStarts();
            case ScheduledEvent::EVENT_TYPE_CEREMONY:
                return $this->competition->getCeremony();
            default:
                return null;
        }
    }

    /**
     * Gets the title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->competition->getEvent()->getName();
    }

    /**
     * Gets the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}