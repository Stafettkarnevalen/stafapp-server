<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 21/10/2017
 * Time: 13.44
 */

namespace App\Entity\Interfaces;


use App\Entity\Security\Group;

/**
 * Interface RequiresGroupInterface
 * @package App\Entity\Interfaces
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
interface RequiresGroupInterface
{
    /**
     * Gets the group.
     *
     * @return Group
     */
    public function getGroup();

    /**
     * Sets the group.
     *
     * @param Group $group
     * @return $this
     */
    public function setGroup(Group $group);

    /**
     * Gets the Group's name.
     *
     * @return string
     */
    public function getGroupName();

    /**
     * Gets the Group's email.
     *
     * @return string
     */
    public function getGroupEmail();

    /**
     * Gets the Group's login route.
     *
     * @return string
     */
    public function getGroupLoginRoute();

    /**
     * Gets the Group's logout route.
     *
     * @return string
     */
    public function getGroupLogoutRoute();

    /**
     * Gets the Group's isGoogleSynced flag.
     *
     * @return boolean
     */
    public function getGroupIsGoogleSynced();

}