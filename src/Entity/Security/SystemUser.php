<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 01/01/2018
 * Time: 2.37
 */

namespace App\Entity\Security;

use App\Entity\Schools\SchoolUnit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SystemUser extends User
{

    /**
     * SystemUser constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @var ArrayCollection The schools that this user manages
     */
    protected $schoolUnits;

    /**
     * Adds a school unit.
     *
     * @param SchoolUnit $schoolUnit The school unit to add
     *
     * @return $this
     */
    public function addSchoolUnit(SchoolUnit $schoolUnit)
    {
        if ($this->schoolUnits->contains($schoolUnit)) {
            return $this;
        }
        $this->schoolUnits->add($schoolUnit);
        return $this;
    }

    /**
     * Removes a school.
     *
     * @param SchoolUnit $schoolUnit The school unit to remove
     *
     * @return $this
     */
    public function removeSchoolUnit(SchoolUnit $schoolUnit)
    {
        if (!$this->schoolUnits->contains($schoolUnit)) {
            return $this;
        }
        $this->schoolUnits->removeElement($schoolUnit);
        return $this;
    }

    /**
     * Checks if this user manages a school unit.
     *
     * @param SchoolUnit $schoolUnit The school unit to check for
     *
     * @return bool
     */
    public function hasSchoolUnit($schoolUnit)
    {
        if (!$schoolUnit instanceof SchoolUnit) {
            return false;
        }
        return $this->schoolUnits->contains($schoolUnit);
    }

    /**
     * Gets the school units.
     *
     * @return ArrayCollection
     */
    public function getSchoolUnits()
    {
        return $this->schoolUnits;
    }

    /**
     * Sets the school units.
     *
     * @param ArrayCollection $schoolUnits The school units to manage
     *
     * @return $this
     */
    public function setSchoolUnits($schoolUnits)
    {
        $this->schoolUnits = $schoolUnits;

        return $this;
    }

}