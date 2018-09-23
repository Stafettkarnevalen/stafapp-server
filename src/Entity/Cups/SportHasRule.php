<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 08/06/2018
 * Time: 18.58
 */

namespace App\Entity\Cups;
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
 * @ORM\Table(name="sport_has_rule_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Cups
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SportHasRule
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
     * @ORM\ManyToOne(targetEntity="Sport", inversedBy="hasRules")
     * @ORM\JoinColumn(name="sport_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Sport $sport The Sport
     */
    protected $sport;

    /**
     * @ORM\ManyToOne(targetEntity="SportRule", inversedBy="hasSports")
     * @ORM\JoinColumn(name="rule_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var SportRule $rule The Rule
     */
    protected $rule;

    /**
     * Gets the sport.
     *
     * @return Sport
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Sets the sport.
     *
     * @param Sport $sport
     * @return $this
     */
    public function setSport(Sport $sport)
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * Gets the rule.
     *
     * @return SportRule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Sets the rule.
     *
     * @param SportRule $rule
     * @return $this
     */
    public function setRule(SportRule $rule)
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
        $rules = $this->sport->getHasRules();
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $rules->matching($criteria);
    }

}