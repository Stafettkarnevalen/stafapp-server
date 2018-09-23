<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 14.23
 */

namespace App\Entity\Relays;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\OrderedEntityInterface;
use App\Entity\Interfaces\Serializable;
use App\Entity\Traits\LoggableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Schools\SchoolUnit;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\VersionedOrderedEntityTrait;
use App\Entity\Traits\PersistencyDataTrait;

/**
 * @ORM\Table(name="member_of_team_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @UniqueEntity(fields="team,competitor, round", message="member.exists")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Relays
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class MemberOfTeam implements Serializable, CreatedByUserInterface, LoggableEntity, OrderedEntityInterface
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** use fields trait */
    use FieldsTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use ordered entity trait */
    use VersionedOrderedEntityTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="members")
     * @ORM\JoinColumn(name="team_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Team $team The team
     */
    protected $team;

    /**
     * @ORM\ManyToOne(targetEntity="Competitor", inversedBy="teams")
     * @ORM\JoinColumn(name="competitor_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Competitor $competitor The competitor
     */
    protected $competitor;

    /**
     * @ORM\ManyToOne(targetEntity="Round")
     * @ORM\JoinColumn(name="round_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Round $round The round that this membership is applicable for
     */
    protected $round;

    /**
     * Gets the leg.
     *
     * @return integer
     */
    public function getLeg()
    {
        return $this->getOrder() + 1;
    }

    /**
     * Sets the leg.
     *
     * @param integer $leg
     * @return $this
     */
    public function setLeg($leg) {
        return $this->setOrder($leg - 1);
    }

    /**
     * Gets the Team.
     *
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Sets the Team.
     *
     * @param Team $team
     * @return $this
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Gets the Competitor.
     *
     * @return Competitor
     */
    public function getCompetitor()
    {
        return $this->competitor;
    }

    /**
     * Sets the Competitor.
     *
     * @param Competitor $competitor
     * @return $this
     */
    public function setCompetitor($competitor)
    {
        $this->competitor = $competitor;

        return $this;
    }

    /**
     * Gets the Round.
     *
     * @return Round
     */
    public function getRound()
    {
        return $this->round;
    }

    /**
     * Sets the Round.
     *
     * @param Round $round
     * @return $this
     */
    public function setRound($round)
    {
        $this->round = $round;

        return $this;
    }


    /**
     * Gets class of.
     *
     * @return integer
     */
    public function getClassOf()
    {
        return $this->getCompetitor()->getClassOf();
    }

    /**
     * Gets the gender.
     *
     * @return string
     */
    public function getGender()
    {
        return $this->getCompetitor()->getGender();
    }

    /**
     * Gets the SchoolUnit
     *
     * @return SchoolUnit
     */
    public function getSchoolUnit()
    {
        return $this->getCompetitor()->getSchoolUnit();
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getCompetitor()->getFirstname();
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->getCompetitor()->getLastname();
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

    /**
     * Gets the siblings of this ordered entity.
     *
     * @param ObjectManager $em
     * @return ArrayCollection
     */
    public function getSiblings(ObjectManager $em = null)
    {
        $members = $this->getTeam()->getMembers();
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $members->matching($criteria);
    }
}