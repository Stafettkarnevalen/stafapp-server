<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 3.29
 */

namespace App\Entity\Relays;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\OrderedEntityInterface;
use App\Entity\Traits\LoggableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\NotesTrait;
use App\Entity\Traits\OrderedEntityTrait;
use App\Entity\Traits\PersistencyDataTrait;

/**
 * @ORM\Table(name="race_result_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @UniqueEntity(fields="heat,team", message="result.exists")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Relays
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class RaceResult implements \Serializable, CreatedByUserInterface, LoggableEntity, OrderedEntityInterface
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** use fields trait */
    use FieldsTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use notes field */
    use NotesTrait;

    /** Use ordered entity trait */
    use OrderedEntityTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @const MARK_AUTOMATIC_QUALIFIER This result entitles to automatic qualification based on the rank in the heat
     */
    const MARK_AUTOMATIC_QUALIFIER     = "Q";

    /**
     * @const MARK_SECONDARY_QUALIFIER This result entitles to a qualification based on the time
     */
    const MARK_SECONDARY_QUALIFIER     = "q";

    /**
     * @const MARK_NON_WINNING_TIME The result was a non winning time
     */
    const MARK_NON_WINNING_TIME        = 'n';

    /**
     * @const MARK_DID_NOT_START The team never started the race
     */
    const MARK_DID_NOT_START           = "DNS";

    /**
     * @const MARK_DID_NOT_FINISH The team never finished the race
     */
    const MARK_DID_NOT_FINISH          = "DNF";

    /**
     * @const MARK_DISQUALIFIED The team was disqualified
     */
    const MARK_DISQUALIFIED            = "DQ";

    /**
     * @const MARK_GAME_RECORD The result was a game record
     */
    const MARK_GAME_RECORD             = "GR";

    /**
     * @const MARK_GAME_RECORD The result was an area record
     */
    const MARK_AREA_RECORD             = "AR";

    /**
     * @const MARK_PERSONAL_BEST The result was a personal best
     */
    const MARK_PERSONAL_BEST           = "PB";

    /**
     * @const MARK_OUTSIDE_THE_COMPETITION The team is participating outside the competition
     */
    const MARK_OUTSIDE_THE_COMPETITION = "OTC";

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="rank_fld", type="integer", nullable=true)
     * @var integer $rank The rank of the result
     */
    private $rank;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="time_fld", type="integer", nullable=true)
     * @var integer $time The time in milliseconds
     */
    private $time;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="lane_fld", type="integer", nullable=false)
     * @Assert\NotBlank()
     * @var integer $lane The lane where the result was run
     */
    private $lane;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="marks_fld", type="string", columnDefinition="SET('Q', 'n', 'DNS', 'DNF', 'DQ', 'GR', 'AR', 'PB', 'OTC')", nullable=true)
     * @Assert\Choice(choices={"Q", "q", "n", "DNS", "DNF", "DQ", "GR", "AR", "PB", "OTC"}, multiple=true)
     * @var string $marks The marks that the result was entitled to
     */
    private $marks;

    /**
     * @ORM\ManyToOne(targetEntity="Heat", inversedBy="results")
     * @ORM\JoinColumn(name="heat_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Heat $heat The Heat of the Result
     */
    private $heat;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="results")
     * @ORM\JoinColumn(name="team_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Team $team The team that earned the result
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity="RaceResultAction", mappedBy="result", cascade={"persist", "merge", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     * @var ArrayCollection $actions The actions taken by the owning team of this result
     */
    protected $actions;

    /**
     * Gets the rank.
     *
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Sets the rank.
     *
     * @param integer $rank
     * @return $this
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Gets the time (in milli seconds).
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Sets the time (in milli seconds).
     *
     * @param integer $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Gets the lane.
     *
     * @return integer
     */
    public function getLane()
    {
        return $this->lane;
    }

    /**
     * Sets the lane.
     *
     * @param integer $lane
     * @return $this
     */
    public function setLane($lane)
    {
        $this->lane = $lane;

        return $this;
    }

    /**
     * Gets the marks.
     *
     * @return array
     */
    public function getMarks()
    {
        if (!empty($this->marks))
            return explode(',', $this->marks);
        return [];
    }

    /**
     * Sets the marks.
     *
     * @param array $marks
     * @return $this
     */
    public function setMarks(array $marks)
    {
        $this->marks = implode(',', $marks);

        return $this;
    }

    /**
     * Gets the Heat.
     *
     * @return Heat
     */
    public function getHeat()
    {
        return $this->heat;
    }

    /**
     * Sets the Heat.
     *
     * @param Heat $heat
     * @return $this
     */
    public function setHeat($heat)
    {
        $this->heat = $heat;

        return $this;
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
     * Gets the siblings of this ordered entity.
     *
     * @param ObjectManager $em
     * @return ArrayCollection
     */
    public function getSiblings(ObjectManager $em = null)
    {
        $results = $this->getHeat()->getResults();
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $results->matching($criteria);
    }
}