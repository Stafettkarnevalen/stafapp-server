<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/11/2017
 * Time: 17.11
 */

namespace App\Entity\Cheerleading;

use App\Entity\Interfaces\ScheduledEvent;

/**
 * Class CheerleadingSchedule
 * @package App\Entity\Cheerleading
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class CheerleadingSchedule implements ScheduledEvent
{
    /** @var CheerleadingCompetition $competition The competition that is responsible for this scheduled event */
    protected $competition;

    /** @var string $type The type of the scheduled event */
    protected $type;

    /**
     * @var boolean $chanting True if this schedule concerns the chant competition rather than the
     *                        cheerleading competition
     */
    protected $chanting;

    /**
     * ScheduledCheerleadingEvent constructor.
     * @param CheerleadingCompetition $competition
     * @param bool $chanting
     * @param string $type
     */
    public function __construct(CheerleadingCompetition $competition, $chanting = false, $type)
    {
        $this->competition = $competition;
        $this->type = $type;
        $this->chanting = $chanting;
    }

    /**
     * Gets the start time.
     *
     * @return \DateTime|null
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
     * @return string
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