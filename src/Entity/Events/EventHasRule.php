<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 08/06/2018
 * Time: 18.58
 */

namespace App\Entity\Events;

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
 * @ORM\Table(name="event_has_rule_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Events
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */

class EventHasRule
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
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="hasRules")
     * @ORM\JoinColumn(name="event_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Event $event The Event
     */
    protected $event;

    /**
     * @ORM\ManyToOne(targetEntity="EventRule", inversedBy="hasEvents")
     * @ORM\JoinColumn(name="rule_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var EventRule $rule The Rule
     */
    protected $rule;

    /**
     * Gets the event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Sets the event.
     *
     * @param Event $event
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Gets the rule.
     *
     * @return EventRule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Sets the rule.
     *
     * @param EventRule $rule
     * @return $this
     */
    public function setRule(EventRule $rule)
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
        $rules = $this->event->getHasRules();
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $rules->matching($criteria);
    }

}