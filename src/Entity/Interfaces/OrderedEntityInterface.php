<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 28/10/2017
 * Time: 19.01
 */

namespace App\Entity\Interfaces;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Interface OrderedEntityInterface
 * @package App\Entity\Interfaces
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
interface OrderedEntityInterface
{
    /**
     * Gets the order.
     *
     * @return mixed
     */
    public function getOrder();

    /**
     * Sets the order.
     *
     * @param mixed $order
     * @return $this
     */
    public function setOrder($order);

    /**
     * Gets the siblings of this entity.
     *
     * @param ObjectManager $em|null
     * @return ArrayCollection
     */
    public function getSiblings(ObjectManager $em = null);

    /**
     * Gets the siblings of this entity with a higher order than this entity.
     *
     * @param ObjectManager $em|null
     * @return ArrayCollection
     */
    public function getSiblingsAfter(ObjectManager $em = null);

    /**
     * Gets the siblings of this entity with a lower order than this entity.
     *
     * @param ObjectManager $em|null
     * @return ArrayCollection
     */
    public function getSiblingsBefore(ObjectManager $em = null);

}