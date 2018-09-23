<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/11/2017
 * Time: 17.11
 */
namespace App\Entity\Schedule;

use App\Entity\Interfaces\ScheduledEvent;

/**
 * Class EventSchedule
 * @package App\Entity\Event
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class ProgramSchedule implements ScheduledEvent
{
    /** @var  Program $program The program that is responsible for this scheduled event */
    protected $program;

    /** @var  string $type The type of the scheduled event*/
    protected $type;

    /**
     * ProgramSchedule constructor.
     * @param $program
     * @param $type
     */
    public function __construct($program, $type)
    {
        $this->program = $program;
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
                return $this->program->getStarts();
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
        return $this->program->getTitle();
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