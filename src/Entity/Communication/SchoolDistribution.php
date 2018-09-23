<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/09/2017
 * Time: 15.01
 */

namespace App\Entity\Communication;


use App\Entity\Interfaces\MessageDistributionInterface;
use App\Entity\Schools\School;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class SchoolDistribution
 * @package App\Entity\Communication
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SchoolDistribution implements MessageDistributionInterface
{
    /**
     * @var School $school The school that serves as the distribution.
     *                     All managers of this school will get the message.
     */
    protected $school;

    /**
     * SchoolDistribution constructor.
     * @param School $school
     */
    public function __construct(School $school)
    {
        $this->school = $school;
    }

    /**
     * Gets the actual users that get this message.
     *
     * @return ArrayCollection
     */
    public function getUsers() {
        return $this->school->getManagers();
    }

    /**
     * Gets a label for this distribution.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->school->getName();//->getName();
    }

    /**
     * Gets the Entity class behing this distribution.
     * @return string
     */
    public function getClass()
    {
        return School::class;
    }

    /**
     * Gets the id of the Entity behind this distribution.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->school->getId();
    }

    /**
     * Returns a distribution of an array of SchoolDistributions.
     *
     * @param array $schools
     * @return array
     */
    public static function all(array $schools)
    {
        $all = [];
        foreach ($schools as $school) {
            $all[] = new SchoolDistribution($school);
        }
        return $all;
    }

    /**
     * Gets the class and id combination of the Entity behind this distribution.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->getClass() . '-' . $this->getId();
    }
}