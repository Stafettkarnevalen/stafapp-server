<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 14.06
 */

namespace App\Entity\Relays;

use App\Entity\Documentation\Documentation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RelayRuleRepository")
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Relays
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class RelayRule extends Documentation
{

    /**
     * @ORM\OneToMany(targetEntity="RelayHasRule", mappedBy="rule", cascade={"persist", "merge", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     * @var ArrayCollection $hasRelays The relays that this rule affects
     */
    protected $hasRelays;

    /**
     * RelayRule constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->hasRelays = new ArrayCollection();
    }

    /**
     * Gets the Relays.
     *
     * @return ArrayCollection
     */
    public function getRelays()
    {
        $relays = new ArrayCollection();
        foreach ($this->hasRelays as $relayHasRule)
            $relays->add($relayHasRule->getRelay());
        return $relays;
    }


    /**
     * Add a relay to this rule.
     *
     * @param Relay $relay
     * @return $this
     */
    public function addRelay(Relay $relay)
    {
        if ($this->hasRelay($relay)) {
            return $this;
        }
        $hasRelay = new RelayHasRule();
        $hasRelay->setRelay($relay)->setRule($this)->setOrder($relay->getHasRules()->count());
        return $this;
    }

    /**
     * Removes a relay from this rule.
     *
     * @param Relay $relay
     * @return $this
     */
    public function removeRelay(Relay $relay)
    {
        if (!$this->hasRelay($relay)) {
            return $this;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('relay', $relay))
            ->andWhere(Criteria::expr()->neq('rule', $this))
        ;

        $this->hasRelays = $this->hasRelays->matching($criteria);

        return $this;
    }

    /**
     * Checks if relay has a rule
     *
     * @param Relay $relay The relay to check for
     *
     * @return bool
     */
    public function hasRelay(Relay $relay)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('relay', $relay))
            ->andWhere(Criteria::expr()->eq('rule', $this))
        ;

        return ($this->hasRelays->matching($criteria)->count() == 1);
    }

    /**
     * Gets the hasRelays.
     *
     * @return ArrayCollection
     */
    public function getHasRelays()
    {
        return $this->hasRelays;
    }

    /**
     * Sets the hasRelays.
     *
     * @param ArrayCollection $hasRelays
     * @return $this
     */
    public function setHasRelays(ArrayCollection $hasRelays)
    {
        $this->hasRelays = $hasRelays;

        return $this;
    }


}