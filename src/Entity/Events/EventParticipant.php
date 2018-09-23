<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 13.35
 */

namespace App\Entity\Events;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\Serializable;
use App\Entity\Traits\LoggableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Schools\SchoolUnit;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\VersionedPersonTrait;
use App\Entity\Traits\SeasonTrait;

/**
 * @ORM\Table(name="event_participant_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Event
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class EventParticipant implements Serializable, CreatedByUserInterface, LoggableEntity
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** use fields trait */
    use FieldsTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /** Use person trait */
    use VersionedPersonTrait;

    /** Use season field */
    use SeasonTrait;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="class_of_fld", type="integer", columnDefinition="INT(11)", nullable=false)
     * @Assert\NotBlank()
     * @var integer $classOf The class of the participant
     */
    protected $classOf;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schools\SchoolUnit", inversedBy="eventParticipants")
     * @ORM\JoinColumn(name="school_unit_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var SchoolUnit $schoolUnit The SchoolUnit the participant belongs to
     */
    protected $schoolUnit;

    /**
     * @ORM\OneToMany(targetEntity="MemberOfEventSquad", mappedBy="participant", cascade={"persist", "remove"})
     * @var ArrayCollection $squads The event squads the participant belongs to
     */
    protected $squads;

    /**
     * Gets class of.
     *
     * @return integer
     */
    public function getClassOf()
    {
        return $this->classOf;
    }

    /**
     * Sets class of.
     *
     * @param integer $classOf
     * @return $this
     */
    public function setClassOf($classOf)
    {
        $this->classOf = $classOf;

        return $this;
    }

    /**
     * Gets the SchoolUnit.
     *
     * @return SchoolUnit
     */
    public function getSchoolUnit()
    {
        return $this->schoolUnit;
    }

    /**
     * Sets the SchoolUnit.
     *
     * @param SchoolUnit $schoolUnit
     * @return $this
     */
    public function setSchoolUnit($schoolUnit)
    {
        $this->schoolUnit = $schoolUnit;

        return $this;
    }

    /**
     * Gets the Squads.
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
     * Gets a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFullname();
    }
}