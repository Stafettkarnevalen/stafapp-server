<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 11.01
 */

namespace App\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait OrderedEntityTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait OrderedEntityTrait
{
    /**
     * @ORM\Column(name="order_fld", type="integer")
     * @Assert\NotBlank()
     * @var integer $order The order or index of the entity
     */
    protected $order;

    /**
     * Gets the order.
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets the order.
     *
     * @param integer $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Gets the siblings of this ordered entity.
     *
     * @param ObjectManager|null $em
     * @return ArrayCollection
     */
    abstract public function getSiblings(ObjectManager $em = null);

    /**
     * Gets the siblings after this ordered entity.
     *
     * @param ObjectManager $em
     * @return ArrayCollection
     */
    public function getSiblingsAfter(ObjectManager $em = null)
    {
        $siblings = $this->getSiblings($em);
        $criteria = Criteria::create()->where(Criteria::expr()->gt('order', $this->getOrder()))->orderBy(['order' => 'ASC']);
        return $siblings->matching($criteria);
    }

    /**
     * Gets the siblings before this ordered entity.
     *
     * @param ObjectManager $em
     * @return ArrayCollection
     */
    public function getSiblingsBefore(ObjectManager $em = null)
    {
        $siblings = $this->getSiblings($em);
        $criteria = Criteria::create()->where(Criteria::expr()->lt('order', $this->getOrder()))->orderBy(['order' => 'ASC']);
        return $siblings->matching($criteria);
    }
}