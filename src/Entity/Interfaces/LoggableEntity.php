<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 29/10/2017
 * Time: 10.30
 */

namespace App\Entity\Interfaces;

/**
 * Interface LoggableEntity
 * @package App\Entity\Interfaces
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
interface LoggableEntity
{
    /**
     * Gets the id of the loggable entity.
     */
    public function getId();
}