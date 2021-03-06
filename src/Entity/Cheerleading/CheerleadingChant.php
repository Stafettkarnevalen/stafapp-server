<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 05/06/2017
 * Time: 1.05
 */
namespace App\Entity\Cheerleading;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\OrderedEntityInterface;
use App\Entity\Interfaces\Serializable;
use App\Entity\Traits\LoggableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\OrderedEntityTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\VersionedTitleAndTextTrait;


/**
 * @ORM\Table(name="cheerleading_chant_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Cheerleading
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class CheerleadingChant implements Serializable, CreatedByUserInterface, LoggableEntity, OrderedEntityInterface
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** use fields trait */
    use FieldsTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** use ordered trait */
    use OrderedEntityTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /** use title and text fields trait */
    use VersionedTitleAndTextTrait;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="melody_fld", type="string", length=128, nullable=false)
     * @Assert\NotBlank()
     * @var string $melody The name of the melody that this chant is using
     */
    protected $melody;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="author_fld", type="string", length=128, nullable=true)
     * @var string $author The author of the chant.
     */
    protected $author;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="video_url_fld", type="string", length=256, nullable=true)
     * @var string $videoUrl The URL that the chant can be seen on.
     */
    protected $videoUrl;

    /**
     * @ORM\Column(name="rank_fld", type="integer", nullable=true)
     * @var integer $rank The rank of the squad (1 = winner, 2 = runner up, 0 or null = unspecified)
     */
    protected $rank;

    /**
     * @ORM\Column(name="explanation_fld", type="text", nullable=true)
     * @var string $explanation An explanation about the rank given
     */
    protected $explanation;

    /**
     * @ORM\ManyToOne(targetEntity="CheerleadingSquad", inversedBy="chants")
     * @ORM\JoinColumn(name="squad_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var CheerleadingSquad $squad The squad responsible for the chant
     */
    protected $squad;

    /**
     * @ORM\ManyToOne(targetEntity="ChantCompetition", inversedBy="chants")
     * @ORM\JoinColumn(name="competition_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var ChantCompetition $competition The competition
     */
    protected $competition;

    /**
     * Gets the melody.
     *
     * @return string
     */
    public function getMelody()
    {
        return $this->melody;
    }

    /**
     * Sets the melody.
     *
     * @param string $melody
     * @return $this
     */
    public function setMelody($melody)
    {
        $this->melody = $melody;

        return $this;
    }

    /**
     * Gets the author.
     *
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the author.
     *
     * @param string|null $author
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Gets the video url.
     *
     * @return string|null
     */
    public function getVideoUrl()
    {
        return $this->videoUrl;
    }

    /**
     * Sets the video url.
     *
     * @param string|null $videoUrl
     * @return $this
     */
    public function setVideoUrl($videoUrl)
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    /**
     * Gets the squad.
     *
     * @return CheerleadingSquad
     */
    public function getSquad()
    {
        return $this->squad;
    }

    /**
     * Sets the squad.
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
     * Gets the competition.
     *
     * @return ChantCompetition
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * Sets the competition.
     *
     * @param ChantCompetition $competition
     * @return $this
     */
    public function setCompetition($competition)
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * Gets the rank,
     *
     * @return integer|null
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Sets the rank.
     *
     * @param integer|null $rank
     * @return $this
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Gets the explanation.
     *
     * @return string|null
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Sets the explanation.
     *
     * @param string|null $explanation
     * @return $this
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

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
        $chants = $this->getSquad()->getChants();
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $chants->matching($criteria);
    }
}