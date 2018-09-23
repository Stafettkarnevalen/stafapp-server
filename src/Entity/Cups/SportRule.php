<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 14.06
 */

namespace App\Entity\Cups;

use App\Entity\Documentation\Documentation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="sport_rule_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity(repositoryClass="App\Repository\SportRuleRepository")
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Cups
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SportRule extends Documentation
{

    /**
     * @ORM\OneToMany(targetEntity="SportHasRule", mappedBy="rule", cascade={"persist", "merge", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     * @var ArrayCollection $hasSports The sports that this rule affects
     */
    protected $hasSports;

    /**
     * SportRule constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->hasSports = new ArrayCollection();
    }

    /**
     * Gets the Sports.
     *
     * @return ArrayCollection
     */
    public function getSports()
    {
        $sports = new ArrayCollection();
        foreach ($this->hasSports as $sportHasRule)
            $sports->add($sportHasRule->getSport());
        return $sports;
    }


    /**
     * Add a Sport to this rule.
     *
     * @param Sport $sport
     * @return $this
     */
    public function addSport(Sport $sport)
    {
        if ($this->hasSport($sport)) {
            return $this;
        }
        $hasSport = new SportHasRule();
        $hasSport->setSport($sport)->setRule($this)->setOrder($sport->getHasRules()->count());
        return $this;
    }

    /**
     * Removes a Sport from this rule.
     *
     * @param Sport $sport
     * @return $this
     */
    public function removeSport(Sport $sport)
    {
        if (!$this->hasSport($sport)) {
            return $this;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('sport', $sport))
            ->andWhere(Criteria::expr()->neq('rule', $this))
        ;

        $this->hasSports = $this->hasSports->matching($criteria);

        return $this;
    }

    /**
     * Checks if Sport has a rule
     *
     * @param Sport $sport The Sport to check for
     *
     * @return bool
     */
    public function hasSport(Sport $sport)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('sport', $sport))
            ->andWhere(Criteria::expr()->eq('rule', $this))
        ;

        return ($this->hasSports->matching($criteria)->count() == 1);
    }

    /**
     * Gets the hasSports.
     *
     * @return ArrayCollection
     */
    public function getHasSports()
    {
        return $this->hasSports;
    }

    /**
     * Sets the hasSports.
     *
     * @param ArrayCollection $hasSports
     * @return $this
     */
    public function setHasSports(ArrayCollection $hasSports)
    {
        $this->hasSports = $hasSports;

        return $this;
    }


}