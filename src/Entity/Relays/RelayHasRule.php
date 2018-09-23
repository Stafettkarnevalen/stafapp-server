<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 08/06/2018
 * Time: 18.58
 */

namespace App\Entity\Relays;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\LoggableTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\VersionedOrderedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="relay_has_rule_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Relays
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */

class RelayHasRule
{
    /** use fields trait */
    use FieldsTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use ordered entity trait */
    use VersionedOrderedEntityTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Relay", inversedBy="hasRules")
     * @ORM\JoinColumn(name="relay_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Relay $relay The Relay
     */
    protected $relay;

    /**
     * @ORM\ManyToOne(targetEntity="RelayRule", inversedBy="hasRelays")
     * @ORM\JoinColumn(name="rule_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var RelayRule $rule The Rule
     */
    protected $rule;

    /**
     * Gets the relay.
     *
     * @return Relay
     */
    public function getRelay()
    {
        return $this->relay;
    }

    /**
     * Sets the relay.
     *
     * @param Relay $relay
     * @return $this
     */
    public function setRelay(Relay $relay)
    {
        $this->relay = $relay;

        return $this;
    }

    /**
     * Gets the rule.
     *
     * @return RelayRule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Sets the rule.
     *
     * @param RelayRule $rule
     * @return $this
     */
    public function setRule(RelayRule $rule)
    {
        $this->rule = $rule;

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
        if ($em) {
            $em = null;
        }
        $rules = $this->relay->getHasRules();
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $rules->matching($criteria);
    }

}