<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 11.05
 */
namespace App\Entity\Cheerleading;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\Serializable;
use App\Entity\Schools\SchoolType;
use App\Entity\Services\ServiceCategory;
use App\Entity\Traits\CloneableTrait;
use App\Entity\Traits\LoggableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\VersionedAbbreviationTrait;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\VersionedLifespanTrait;
use App\Entity\Traits\VersionedNameTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\VersionedSchoolClassSpanTrait;
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="cheerleading_event_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Cheerleading
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class CheerleadingEvent implements Serializable, CreatedByUserInterface, LoggableEntity
{
    /** Use abbreviation field */
    use VersionedAbbreviationTrait;

    /** use created by user trait */
    use CreatedByUserTrait;

    /** Use cloning functions */
    use CloneableTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use lifespan fields */
    use VersionedLifespanTrait;

    /** Use name field */
    use VersionedNameTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /** Use school's min and max class of fields */
    use VersionedSchoolClassSpanTrait;

    /**
     * @Gedmo\Slug(fields={"name"}, style="lower", separator=".", unique=false)
     * @ORM\Column(name="email_slug_fld", type="string", length=80, nullable=false)
     * @var string $emailSlug A slug used by the email addresser generated for competitions of this event
     */
    protected $emailSlug;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="max_size_fld", type="integer", columnDefinition="INT(11) UNSIGNED", nullable=false)
     * @Assert\NotBlank()
     * @var integer $maxSize The maximum size of a squad.
     */
    protected $maxSize;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Services\ServiceCategory", inversedBy="serviceTypes")
     * @ORM\JoinColumn(name="service_category_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var ServiceCategory $serviceCategory The category of this service type
     */
    protected $serviceCategory;

    /**
     * @ORM\OneToMany(targetEntity="CheerleadingCompetition", mappedBy="event", cascade={"persist", "remove"})
     * @var ArrayCollection $competitions The competitions based on this event
     */
    protected $competitions;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Schools\SchoolType", mappedBy="cheerleadingEvents", cascade={"persist", "merge"})
     * @ORM\OrderBy({"order" = "ASC"})
     * @var ArrayCollection $schoolTypes The school types allowed to take part in this event
     */
    protected $schoolTypes;

    /**
     * @ORM\OneToMany(targetEntity="CheerleadingHasRule", mappedBy="event", cascade={"persist", "merge", "remove"})
     * @var ArrayCollection $hasRules A collection of rules for this event
     */
    protected $hasRules;

    /**
     * Gets the emailSlug.
     *
     * @return string
     */
    public function getEmailSlug()
    {
        return $this->emailSlug;
    }

    /**
     * Sets the emailSlug.
     *
     * @param string $emailSlug
     * @return $this
     */
    public function setEmailSlug($emailSlug)
    {
        $this->emailSlug = $emailSlug;

        return $this;
    }

    /**
     * Gets the serviceCategory.
     *
     * @return ServiceCategory
     */
    public function getServiceCategory()
    {
        return $this->serviceCategory;
    }

    /**
     * Sets the serviceCategory.
     *
     * @param ServiceCategory $serviceCategory
     * @return $this
     */
    public function setServiceCategory($serviceCategory)
    {
        $this->serviceCategory = $serviceCategory;

        return $this;
    }

    /**
     * Gets the SchoolTypes.
     *
     * @param boolean $array Return value should be an array instead of an ArrayCollection
     * @return ArrayCollection|array
     */
    public function getSchoolTypes($array = true)
    {
        return $array ?
            $this->schoolTypes->toArray() :
            $this->schoolTypes;
    }

    /**
     * Returns the school types formatted in a string
     *
     * @param bool $abbrev
     * @param integer $cutAfter
     * @return string
     */
    public function getSchoolTypesAsString($abbrev = false, $cutAfter = 0)
    {
        $str = [];
        /** @var SchoolType $schoolType */
        foreach ($this->schoolTypes as $i => $schoolType) {
            $str[] = ($abbrev ? $schoolType->getAbbreviation() : $schoolType->getName());
            if ($cutAfter && $i == $cutAfter - 1 && $this->schoolTypes->count() > $cutAfter) {
                $str[] = '...';
                break;
            }
        }
        return implode(', ', $str);
    }

    /**
     * Sets the SchoolTypes.
     *
     * @param ArrayCollection $schoolTypes
     * @return $this
     */
    public function setSchoolTypes($schoolTypes)
    {
        $this->schoolTypes = is_array($schoolTypes) ?
            new ArrayCollection($schoolTypes) :
            $schoolTypes;

        return $this;
    }

    /**
     * Get the hasRules.
     *
     * @return ArrayCollection
     */
    public function getHasRules()
    {
        return $this->hasRules;
    }

    /**
     * Sets the hasRules.
     *
     * @param ArrayCollection $hasRules
     * @return $this
     */
    public function setHasRules($hasRules)
    {
        $this->hasRules = $hasRules;

        return $this;
    }

    /**
     * Gets the max size.
     *
     * @return integer
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }

    /**
     * Sets the max size.
     *
     * @param integer $maxSize
     * @return $this
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    /**
     * Gets the competitions.
     *
     * @return ArrayCollection
     */
    public function getCompetitions()
    {
        return $this->competitions;
    }

    /**
     * Sets the competitions.
     *
     * @param ArrayCollection $competitions
     * @return $this;
     */
    public function setCompetitions($competitions)
    {
        $this->competitions = $competitions;

        return $this;
    }

    /**
     * Gets a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * CheerleadingEvent constructor.
     */
    public function __construct()
    {
        $this->schoolTypes = new ArrayCollection([]);
        $this->competitions = new ArrayCollection([]);
        $this->hasRules = new ArrayCollection([]);
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->name
        ]);
    }

    /**
     * @see \Serializable::unserialize()
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->name) = unserialize($serialized);
    }

    /**
     * Adds a SchoolType.
     *
     * @param SchoolType $schoolType The SchoolType to be added
     * @param bool $cascade If true, add this CheerleadingEvent to the SchoolType as well
     *
     * @return $this
     */
    public function addSchoolType(SchoolType $schoolType, $cascade = true)
    {
        if ($this->schoolTypes->contains($schoolType)) {
            return $this;
        }
        $this->schoolTypes->add($schoolType);
        if ($cascade) $schoolType->addCheerleadingEvent($this, false);
        return $this;
    }

    /**
     * Removes a SchoolType.
     *
     * @param SchoolType $schoolType The SchoolType to be removed
     * @param bool $cascade If true, remove this CheerleadingEvent from the SchoolType as well
     *
     * @return $this
     */
    public function removeSchoolType(SchoolType $schoolType, $cascade = true)
    {
        if (!$this->schoolTypes->contains($schoolType)) {
            return $this;
        }
        $this->schoolTypes->removeElement($schoolType);
        if ($cascade) $schoolType->removeCheerleadingEvent($this, false);
        return $this;
    }

    /**
     * Checks if the CheerleadingEvent has a connection to a SchoolType
     *
     * @param SchoolType|integer $schoolType The SchoolType to check for
     *
     * @return bool
     */
    public function hasSchoolType($schoolType)
    {
        if (is_integer($schoolType)) {
            foreach ($this->schoolTypes as $st)
                if ($st->getId() == $schoolType)
                    return true;
            return false;
        } else if (!$schoolType instanceof schoolType) {
            return false;
        }
        return $this->schoolTypes->contains($schoolType);
    }

    /**
     * Gets the Rules.
     *
     * @return ArrayCollection
     */
    public function getRules()
    {
        $rules = new ArrayCollection();
        foreach ($this->hasRules as $eventHasRule)
            $rules->add($eventHasRule->getRule());
        return $rules;
    }

    /**
     * Checks if event has a rule
     *
     * @param CheerleadingRule $rule The rule to check for
     *
     * @return bool
     */
    public function hasRule(CheerleadingRule $rule)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('event', $this))
            ->andWhere(Criteria::expr()->eq('rule', $rule))
        ;

        return ($this->hasRules->matching($criteria)->count() == 1);
    }

    /**
     * Adds a rule.
     *
     * @param CheerleadingRule $rule The rule to be added
     *
     * @return $this
     */
    public function addRule(CheerleadingRule $rule)
    {
        if ($this->hasRule($rule)) {
            return $this;
        }

        $hasRule = new CheerleadingHasRule();
        $hasRule->setEvent($this)->setRule($rule)->setOrder($this->hasRules->count());
        $this->hasRules->add($hasRule);

        return $this;
    }

    /**
     * Removes a rule.
     *
     * @param CheerleadingRule $rule The rule to be removed
     *
     * @return $this
     */
    public function removeRule(CheerleadingRule $rule)
    {
        if (!$this->hasRule($rule)) {
            return $this;
        }
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('event', $this))
            ->andWhere(Criteria::expr()->neq('rule', $rule))
        ;

        $this->hasRules = $this->hasRules->matching($criteria);

        return $this;
    }
}