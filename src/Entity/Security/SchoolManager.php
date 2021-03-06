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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchoolManagerRepository")
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SchoolManager extends User
{
    /**
     * @ORM\OneToMany(targetEntity="SchoolManagerPosition", mappedBy="manager", cascade={"persist", "remove", "merge"})
     * @var ArrayCollection $positions The positionns within a school unit that this user account has
     */
    protected $positions;

    /**
     * SchoolManager constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->positions = new ArrayCollection();
    }

    /**
     * Adds a school unit.
     *
     * @param SchoolUnit $schoolUnit The school unit to add
     * @param bool $cascade If true, add this user to the school unit as well
     *
     * @return $this
     */
    public function addSchoolUnit(SchoolUnit $schoolUnit, $cascade = true)
    {
        if ($this->hasSchoolUnit($schoolUnit)) {
            return $this;
        }

        $mp = new SchoolManagerPosition();
        $mp->setManager($this);
        $mp->setSchoolUnit($schoolUnit);

        $this->positions->add($mp);

        if ($cascade) $schoolUnit->addManager($this, false);
        return $this;
    }

    /**
     * Removes a school.
     *
     * @param SchoolUnit $schoolUnit The school unit to remove
     * @param bool $cascade If true, remove this user from the school unit as well
     *
     * @return $this
     */
    public function removeSchoolUnit(SchoolUnit $schoolUnit, $cascade = true)
    {
        if (!$this->hasSchoolUnit($schoolUnit)) {
            return $this;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', SchoolManagerPosition::STATUS_RESOLVED))
            ->andWhere(Criteria::expr()->eq('schoolUnit', $schoolUnit))
            ->andWhere(Criteria::expr()->eq('manager', $this));

        $mps = $this->positions->matching($criteria);
        if ($mps->count() !== 1)
            return $this;
        $mp = $mps->first();
        $this->positions->removeElement($mp);

        if ($cascade) $schoolUnit->removeManager($this, false);
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

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', SchoolManagerPosition::STATUS_RESOLVED))
            ->andWhere(Criteria::expr()->eq('schoolUnit', $schoolUnit))
            ->andWhere(Criteria::expr()->eq('manager', $this))
        ;

        return ($this->positions->matching($criteria)->count() == 1);

    }

    /**
     * Gets the school units.
     *
     * @return ArrayCollection
     */
    public function getSchoolUnits()
    {
        $units = new ArrayCollection();
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', SchoolManagerPosition::STATUS_RESOLVED))
            ->andWhere(Criteria::expr()->eq('manager', $this))
        ;

        foreach ($this->positions->matching($criteria) as $mp)
            $units->add($mp);
        return $units;
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
        $positions = new ArrayCollection();
        foreach ($schoolUnits as $unit) {
            $mp = new SchoolManagerPosition();
            $mp->setSchoolUnit($unit)->setManager($this);
            $positions->add($mp);
        }

        return $this->setPositions($positions);
    }

    /**
     * Gets the positions.
     *
     * @return ArrayCollection
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * Sets the positions.
     *
     * @param ArrayCollection $positions
     * @return $this
     */
    public function setPositions(ArrayCollection $positions)
    {
        $this->positions = $positions;

        return $this;
    }
}