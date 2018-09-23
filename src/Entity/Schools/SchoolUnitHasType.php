<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 08/06/2018
 * Time: 18.58
 */

namespace App\Entity\Schools;

use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\LoggableTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\SeasonTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="school_unit_has_type_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Relays
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */

class SchoolUnitHasType
{
    /** use fields trait */
    use FieldsTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use season trait */
    use SeasonTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="pupils_fld", type="json_array", nullable=false)
     * @var array $pupils The number of pupils on each class.
     */
    protected $pupils;

    /**
     * @ORM\ManyToOne(targetEntity="SchoolUnit", inversedBy="hasSchoolTypes")
     * @ORM\JoinColumn(name="school_unit_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var SchoolUnit $schoolUnit The SchoolUnit
     */
    protected $schoolUnit;

    /**
     * @ORM\ManyToOne(targetEntity="SchoolType", inversedBy="hasSchoolUnits")
     * @ORM\JoinColumn(name="school_type_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var SchoolType $schoolType The SchoolType
     */
    protected $schoolType;

    /**
     * SchoolUnitHasType constructor.
     */
    public function __construct()
    {
        $this->pupils = [];
    }

    /**
     * Gets the number of pupils on each class.
     *
     * @return array
     */
    public function getPupils()
    {
        return $this->pupils;
    }

    /**
     * Sets the number of pupils on each class.
     *
     * @param array $pupils
     * @return $this
     */
    public function setPupils(array $pupils)
    {
        $this->pupils = $pupils;

        return $this;
    }

    /**
     * Returns the sum of all pupils in this school unit at this season.
     * @return integer
     */
    public function getSumPupils()
    {
        return array_sum($this->pupils);
    }

    /**
     * Gets the pupils of a specified class.
     *
     * @param integer $class The class
     * @return integer
     */
    public function getPupilsForClass($class)
    {
        if (!$class)
            throw new \UnexpectedValueException();
        if ($class < $this->getSchoolType()->getMinClassOf() || $class > $this->getSchoolType()->getMaxClassOf())
            throw new \OutOfBoundsException();
        return $this->pupils[$class];
    }

    /**
     * Sets the pupils of a specified class.
     *
     * @param integer $class The class
     * @param integer $pupils The number of pupils
     * @return $this
     */
    public function setPupilsForClass($class, $pupils)
    {
        if (!$class)
            throw new \UnexpectedValueException();
        if ($class < $this->getSchoolType()->getMinClassOf() || $class > $this->getSchoolType()->getMaxClassOf())
            throw new \OutOfBoundsException();
        if (!$pupils || $pupils < 0)
            throw new \UnexpectedValueException();

        $this->pupils[$class] = $pupils;

        return $this;
    }

    /**
     * Gets the school unit.
     *
     * @return SchoolUnit
     */
    public function getSchoolUnit()
    {
        return $this->schoolUnit;
    }

    /**
     * Sets the school unit.
     *
     * @param SchoolUnit $schoolUnit
     * @return $this
     */
    public function setSchoolUnit(SchoolUnit $schoolUnit)
    {
        $this->schoolUnit = $schoolUnit;

        return $this;
    }

    /**
     * Gets the school type.
     *
     * @return SchoolType
     */
    public function getSchoolType()
    {
        return $this->schoolType;
    }

    /**
     * Sets the school type.
     *
     * @param SchoolType $schoolType
     * @return $this
     */
    public function setSchoolType(SchoolType $schoolType)
    {
        $this->schoolType = $schoolType;

        return $this;
    }
}