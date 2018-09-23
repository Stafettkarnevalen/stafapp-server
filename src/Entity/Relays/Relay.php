<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 11.05
 */

namespace App\Entity\Relays;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\Serializable;
use App\Entity\Schools\SchoolType;
use App\Entity\Services\Service;
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
 * @ORM\Table(name="relay_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity(repositoryClass="App\Repository\RelayRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Relays
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class Relay implements Serializable, CreatedByUserInterface, LoggableEntity
{
    /** Use abbreviation field */
    use VersionedAbbreviationTrait;

    /** Use cloning functions */
    use CloneableTrait;

    /** use created by user trait */
    use CreatedByUserTrait;

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
     * @const GENDER_MALE The competitors have to be a male
     */
    const GENDER_MALE            = "MALE";

    /**
     * @const GENDER_FEMALE The competitors have to be a female
     */
    const GENDER_FEMALE          = "FEMALE";

    /**
     * @const GENDER_ORDERED_MIXED The competitors can be either but the order matters
     */
    const GENDER_ORDERED_MIXED   = "ORDERED_MIXED";

    /**
     * @const GENDER_UNORDERED_MIXED The competitors can be either and the order makes no difference
     */
    const GENDER_UNORDERED_MIXED = "UNORDERED_MIXED";

    /**
     * @const START_GENDER_MALE The competitor in the opening leg has to be a male
     */
    const START_GENDER_MALE      = "MALE";

    /**
     * @const START_GENDER_FEMALE The competitor in the opening leg has to be a female
     */
    const START_GENDER_FEMALE    = "FEMALE";

    /**
     * @const START_GENDER_ANY The competitor in the opening leg can be either
     */
    const START_GENDER_ANY       = "ANY";

    /**
     * @Gedmo\Slug(fields={"name", "gender"}, style="lower", separator=".", unique=false)
     * @ORM\Column(name="email_slug_fld", type="string", length=80, nullable=false)
     * @var string $emailSlug The email slug for the Group
     */
    protected $emailSlug;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="gender_fld", type="string", columnDefinition="ENUM('MALE', 'FEMALE', 'ORDERED_MIXED', 'UNORDERED_MIXED')", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"MALE", "FEMALE", "ORDERED_MIXED", "UNORDERED_MIXED"})
     * @var string $gender The gender of the competitors allowed to race
     */
    protected $gender;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="start_gender_fld", type="string", columnDefinition="ENUM('MALE', 'FEMALE', 'ANY')", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"MALE", "FEMALE", "ANY"})
     * @var string $startGender The gender of the opening leg
     */
    protected $startGender;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="legs_fld", type="integer", columnDefinition="INT(11) UNSIGNED", nullable=false)
     * @Assert\NotBlank()
     * @var integer $legs The number of legs in the relay
     */
    protected $legs;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="lanes_fld", type="integer", columnDefinition="INT(11) UNSIGNED", nullable=false)
     * @Assert\NotBlank()
     * @var integer $lanes The number of lanes in the relay
     */
    protected $lanes;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="heat_duration_fld", type="integer", columnDefinition="INT(11) UNSIGNED", nullable=false)
     * @Assert\NotBlank()
     * @var integer $heatDuration The duration of one heat in seconds
     */
    protected $heatDuration;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Services\ServiceCategory", inversedBy="serviceTypes")
     * @ORM\JoinColumn(name="service_category_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var ServiceCategory $serviceCategory The category of this service type
     */
    protected $serviceCategory;

    /**
     * @ORM\OneToMany(targetEntity="Race", mappedBy="relay", cascade={"persist", "remove"})
     * @var ArrayCollection $races The races based on this relay (A Race is a yearly event)
     */
    protected $races;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Schools\SchoolType", mappedBy="relays", cascade={"persist", "merge"})
     * @ORM\OrderBy({"order" = "ASC"})
     * @var ArrayCollection $schoolTypes The school types allowed to take part in the relay
     */
    protected $schoolTypes;

    /**
     * @ORM\OneToMany(targetEntity="RelayHasRule", mappedBy="relay", cascade={"persist" ,"merge", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     * @var ArrayCollection $hasRules The rules that apply to this relay
     */
    protected $hasRules;

    /**
     * Gets the emailSlug.
     *
     * @return mixed
     */
    public function getEmailSlug()
    {
        return $this->emailSlug;
    }

    /**
     * Sets the emailSlug.
     *
     * @param mixed $emailSlug
     * @return $this
     */
    public function setEmailSlug($emailSlug)
    {
        $this->emailSlug = $emailSlug;

        return $this;
    }

    /**
     * Gets the gender for this Relay
     *
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Sets the gender for this Relay
     *
     * @param mixed $gender
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Gets the start gender for this Relay
     *
     * @return mixed
     */
    public function getStartGender()
    {
        return $this->startGender;
    }

    /**
     * Sets the start gender for this Relay
     *
     * @param mixed $startGender
     * @return $this
     */
    public function setStartGender($startGender)
    {
        $this->startGender = $startGender;

        return $this;
    }

    /**
     * Gets the number of legs for this Relay
     *
     * @return mixed
     */
    public function getLegs()
    {
        return $this->legs;
    }

    /**
     * Sets the number of legs for this Relay
     *
     * @param mixed $legs
     * @return $this
     */
    public function setLegs($legs)
    {
        $this->legs = $legs;

        return $this;
    }

    /**
     * Gets the number of lanes used in this Relay
     *
     * @return mixed
     */
    public function getLanes()
    {
        return $this->lanes;
    }

    /**
     * Gets the number of lanes used in this Relay
     *
     * @param mixed $lanes
     * @return $this
     */
    public function setLanes($lanes)
    {
        $this->lanes = $lanes;

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
     * Gets the Races.
     *
     * @return ArrayCollection
     */
    public function getRaces()
    {
        return $this->races;
    }

    /**
     * Gets the latest race.
     *
     * @return Race|null
     */
    public function getLatestRace()
    {
        if ($this->races->count()) {
            $races = $this->getRaces()->toArray();
            usort($races, function(Race $r1, Race $r2) {
                return ($r1->getSeason() - $r2->getSeason());
            });
            return array_pop($races);
        }
        return null;
    }

    /**
     * Gets a single race corresponding to a service type (year) and this relay.
     *
     * @param Service $service
     * @return Race|null
     */
    public function getRace($service)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("service", $service));
            // ->andWhere(Criteria::expr()->eq("relay", $this));
        $races = $this->races->matching($criteria);

        if ($races->count() == 1)
            return $races->get(0);
        return null;
    }

    /**
     * Sets the Races.
     *
     * @param mixed $races
     * @return $this
     */
    public function setRaces($races)
    {
        $this->races = $races;

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
        foreach ($this->getSchoolTypes() as $i => $schoolType) {
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
     * @param mixed $schoolTypes
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
     * @param mixed $hasRules
     * @return $this
     */
    public function setHasRules($hasRules)
    {
        $this->hasRules = $hasRules;

        return $this;
    }

    /**
     * Gets the Rules.
     *
     * @return ArrayCollection
     */
    public function getRules()
    {
        $rules = new ArrayCollection();
        foreach ($this->hasRules as $relayHasRule)
            $rules->add($relayHasRule->getRule());
        return $rules;
    }

    /**
     * Checks if relay has a rule
     *
     * @param RelayRule|integer $rule The rule to check for
     *
     * @return bool
     */
    public function hasRule(RelayRule $rule)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('relay', $this))
            ->andWhere(Criteria::expr()->eq('rule', $rule))
        ;

        return ($this->hasRules->matching($criteria)->count() == 1);
    }

    /**
     * Adds a rule.
     *
     * @param RelayRule $rule The rule to be added
     *
     * @return $this
     */
    public function addRule(RelayRule $rule)
    {
        if ($this->hasRule($rule)) {
            return $this;
        }

        $hasRule = new RelayHasRule();
        $hasRule->setRelay($this)->setRule($rule)->setOrder($this->hasRules->count());
        $this->hasRules->add($hasRule);

        return $this;
    }

    /**
     * Removes a rule.
     *
     * @param RelayRule $rule The rule to be removed
     *
     * @return $this
     */
    public function removeRule(RelayRule $rule)
    {
        if (!$this->hasRule($rule)) {
            return $this;
        }
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('relay', $this))
            ->andWhere(Criteria::expr()->neq('rule', $rule))
        ;

        $this->hasRules = $this->hasRules->matching($criteria);

        return $this;
    }


    /**
     * Gets a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName() . ", " . $this->getGender();
    }

    /**
     * Gets the full name of the relay.
     *
     * @param bool $shortGender
     * @param bool $shortClassOf
     * @param bool $showType
     * @return string
     */
    public function getFullName($shortGender = false, $shortClassOf = false, $showType = false)
    {
        $name = $this->getName();
        if ($shortGender) $name .= ', ' . '%short_gender%';
        else $name .= ', ' . '%gender%';
        if ($showType && $this->getSchoolTypes(false)->count()) $name .= ', ' . $this->getSchoolTypes(false)[0]->getName();
        if (!$showType) {
            if ($shortClassOf) $name .= ', ' . $this->getMinClassOf() . '-' . $this->getMaxClassOf();
            else $name .= ', %class_of% ' . $this->getMinClassOf() . ' - ' . $this->getMaxClassOf();
        }
        return $name;
    }

    /**
     * Relay constructor.
     */
    public function __construct()
    {
        $this->schoolTypes = new ArrayCollection([]);
        $this->races = new ArrayCollection([]);
        $this->hasRules = new ArrayCollection([]);
    }

    /**
     * Adds a SchoolType.
     *
     * @param SchoolType $schoolType The SchoolType to be added
     * @param bool $cascade If true, add this Relay to the SchoolType as well
     *
     * @return $this
     */
    public function addSchoolType(SchoolType $schoolType, $cascade = true)
    {
        if ($this->schoolTypes->contains($schoolType)) {
            return $this;
        }
        $this->schoolTypes->add($schoolType);
        if ($cascade) $schoolType->addRelay($this, false);
        return $this;
    }

    /**
     * Removes a SchoolType.
     *
     * @param SchoolType $schoolType The SchoolType to be removed
     * @param bool $cascade If true, remove this Relay from the SchoolType as well
     *
     * @return $this
     */
    public function removeSchoolType(SchoolType $schoolType, $cascade = true)
    {
        if (!$this->schoolTypes->contains($schoolType)) {
            return $this;
        }
        $this->schoolTypes->removeElement($schoolType);
        if ($cascade) $schoolType->removeRelay($this, false);
        return $this;
    }

    /**
     * Checks if the Relay has a connection to a SchoolType
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
     * Gets the heatDuration.
     *
     * @return int
     */
    public function getHeatDuration()
    {
        return $this->heatDuration;
    }

    /**
     * Sets the heatDuration.
     *
     * @param int $heatDuration
     * @return $this
     */
    public function setHeatDuration(int $heatDuration)
    {
        $this->heatDuration = $heatDuration;

        return $this;
    }
}