<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 17/12/2016
 * Time: 11.09
 */

namespace App\Form\School;

use App\Entity\School;
use App\Entity\SchoolName;

class SchoolAndNameWrapper
{
    /** @var  School */
    private $school;

    /** @var  SchoolName */
    private $name;

    /**
     * SchoolAndNameWrapper constructor.
     *
     * @param $school
     * @param $name
     */
    public function __construct($school, $name)
    {
        $this->school = $school;
        $this->name = $name;
    }

    /**
     * Gets the school
     *
     * @return School
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * Sets the school
     *
     * @param School $school
     *
     * @return SchoolAndNameWrapper
     */
    public function setSchool($school)
    {
        $this->school = $school;

        return $this;
    }

    /**
     * Gets the school name
     *
     * @return SchoolName
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the school name
     *
     * @param SchoolName $name
     *
     * @return SchoolAndNameWrapper
     */
    public function setSchoolName($name)
    {
        $this->name = $name;

        return $this;
    }
}