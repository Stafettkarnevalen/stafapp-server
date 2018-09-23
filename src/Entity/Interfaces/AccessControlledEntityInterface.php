<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 21/10/2017
 * Time: 22.16
 */

namespace App\Entity\Interfaces;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Interface AccessControlledEntityInterface
 * @package App\Entity\Interfaces
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
interface AccessControlledEntityInterface
{
    /**
     * Gets the Object Identity for ACLs.
     *
     * @return ObjectIdentity
     */
    public function getObjectIdentity();

    /**
     *  Gets the ACL entries.
     *
     * @return array
     */
    public function getObjectAces();

    /**
     *  Sets the ACL entries.
     *
     * @param array $objectAces
     * @return $this
     */
    public function setObjectAces(array $objectAces);

    /**
     *  Adds to the ACL entries.
     *
     * @param array $objectAces
     * @return $this
     */
    public function addObjectAces(array $objectAces);

    /**
     *  Adds to the ACL entries.
     *
     * @param mixed $objectAce
     * @return $this
     */
    public function addObjectAce($objectAce);

    /**
     *  Updates the ACL entries.
     *
     * @param array $objectAces
     * @return $this
     */
    public function updateObjectAces(array $objectAces);

    /**
     * Sets the default object aces.
     *
     * @return $this
     */
    public function initObjectAces();
}