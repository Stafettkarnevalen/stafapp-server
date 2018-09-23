<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 14.23
 */

namespace App\Entity\Cheerleading;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\Serializable;
use App\Entity\Traits\LoggableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Schools\SchoolUnit;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\PersistencyDataTrait;

/**
 * @ORM\Table(name="member_of_cheerleading_squad_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @UniqueEntity(fields="squad,cheerleader", message="member.exists")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Cheerleading
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class MemberOfCheerleadingSquad implements Serializable, CreatedByUserInterface, LoggableEntity
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** use fields trait */
    use FieldsTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @const RANK_ADULT A rank for an adult supervisor
     */
    const RANK_ADULT   = "ADULT";

    /**
     * @const RANK_CAPTAIN A rank for a squad captain
     */
    const RANK_CAPTAIN = "CAPTAIN";

    /**
     * @const RANK_OFFICER A rank for an officer such as second in command
     */
    const RANK_OFFICER = "OFFICER";

    /**
     * @const RANK_SENIOR A rank for a senior member
     */
    const RANK_SENIOR  = "SENIOR";

    /**
     * @const RANK_MEMBER A rank for a normal member
     */
    const RANK_MEMBER  = "MEMBER";

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="rank_fld", type="string", columnDefinition="ENUM('ADULT', 'CAPTAIN', 'OFFICER', 'SENIOR', 'MEMBER')", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"ADULT", "CAPTAIN", "OFFICER", "SENIOR", "MEMBER"})
     * @var string $rank The rank of the member
     */
    protected $rank;

    /**
     * @ORM\ManyToOne(targetEntity="CheerleadingSquad", inversedBy="members")
     * @ORM\JoinColumn(name="squad_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var CheerleadingSquad $squad The squad of this membership
     */
    protected $squad;

    /**
     * @ORM\ManyToOne(targetEntity="Cheerleader", inversedBy="squads")
     * @ORM\JoinColumn(name="cheer_leader_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Cheerleader $cheerleader The cheerleader of this membership
     */
    protected $cheerleader;

    /**
     * Gets the Squad.
     *
     * @return CheerLeadingSquad
     */
    public function getSquad()
    {
        return $this->squad;
    }

    /**
     * Sets the Squad.
     *
     * @param CheerleadingSquad $squad
     * @return $this
     */
    public function setSquad($squad)
    {
        $this->squad = $squad;

        return $this;
    }

    /**
     * Gets the Cheerleader.
     *
     * @return Cheerleader
     */
    public function getCheerleader()
    {
        return $this->cheerleader;
    }

    /**
     * Sets the Cheerleader.
     *
     * @param Cheerleader $cheerleader
     * @return $this
     */
    public function setCheerleader($cheerleader)
    {
        $this->cheerleader = $cheerleader;

        return $this;
    }

    /**
     * Gets the rank.
     *
     * @return string
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Sets the rank.
     *
     * @param string $rank
     * @return $this
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Gets class of.
     *
     * @return integer
     */
    public function getClassOf()
    {
        return $this->getCheerleader()->getClassOf();
    }

    /**
     * Gets the SchoolUnit
     *
     * @return SchoolUnit
     */
    public function getSchoolUnit()
    {
        return $this->getCheerleader()->getSchoolUnit();
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getCheerleader()->getFirstname();
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->getCheerleader()->getLastname();
    }

    /**
     * Gets the full name of the person
     *
     * @return string
     */
    public function getFullname()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }
}