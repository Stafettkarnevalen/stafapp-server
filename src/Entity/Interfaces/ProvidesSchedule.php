<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 06/11/2017
 * Time: 1.14
 */

namespace App\Entity\Interfaces;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface ProvidesSchedule
 * @package App\Entity\Interfaces
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
interface ProvidesSchedule
{
    /**
     * Gets all ScheduledEvents of this entity.
     *
     * @return ArrayCollection
     */
    public function getSchedule();
}