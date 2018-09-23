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
interface ChronologicalEntityInterface
{
    /**
     * Gets the siblings of this entity.
     *
     * @param ObjectManager $em|null
     * @return ArrayCollection
     */
    public function getSiblings(ObjectManager $em = null);

    /**
     * Gets the predecessors.
     *
     * @param ObjectManager $em|null
     * @return ArrayCollection
     */
    public function getPredecessors(ObjectManager $em = null);

    /**
     * Gets the successors.
     *
     * @param ObjectManager $em|null
     * @return ArrayCollection
     */
    public function getSuccessors(ObjectManager $em = null);

}