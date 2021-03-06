<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/09/2017
 * Time: 14.57
 */

namespace App\Entity\Interfaces;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface MessageDistributionInterface
 * @package App\Entity\Interfaces
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
interface MessageDistributionInterface
{

    /**
     * Gets the actual users that get this message.
     *
     * @return ArrayCollection
     */
    public function getUsers();

    /**
     * Gets a label for this distribution.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Gets the Entity class behing this distribution.
     * @return string
     */
    public function getClass();

    /**
     * Gets the id of the Entity behind this distribution.
     *
     * @return integer
     */
    public function getId();

    /**
     * Gets the class and id combination of the Entity behind this distribution.
     *
     * @return string
     */
    public function getValue();

}