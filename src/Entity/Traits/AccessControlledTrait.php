<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 21/10/2017
 * Time: 21.16
 */

namespace App\Entity\Traits;

use App\Entity\Security\SimpleACE;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Trait AccessControlledTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait AccessControlledTrait
{
    /** @var array access control list entries */
    protected $objectAces = [];

    /**
     * Gets an Object Identity for this entity.
     *
     * @return ObjectIdentity
     */
    public function getObjectIdentity()
    {
        return ObjectIdentity::fromDomainObject($this);
    }

    /**
     * Gets the entity access control list entries.
     *
     * @return array
     */
    public function getObjectAces()
    {
        usort($this->objectAces, function(SimpleACE $ace1, SimpleACE $ace2) { return $ace1->getIndex() - $ace2->getIndex(); });

        return $this->objectAces;
    }

    /**
     * Sets the entity access control list entries.
     *
     * @param array $objectAces
     * @return $this
     */
    public function setObjectAces(array $objectAces)
    {
        $this->objectAces = $objectAces;

        return $this;
    }

    /**
     * Adds to the entity access control list entries.
     *
     * @param array $objectAces
     * @return $this
     */
    public function addObjectAces(array $objectAces)
    {
        foreach ($objectAces as $ace) {
            if (!isset($this->getObjectAces()[$ace->getIndex()])) {
                $this->objectAces[] = $ace;
            }
        }
        return $this;
    }

    /**
     * Updates the entity access control list entries.
     *
     * @param array $objectAces
     * @return $this
     */
    public function updateObjectAces(array $objectAces)
    {
        /** @var SimpleACE $ace */
        foreach ($objectAces as $ace) {
            $this->objectAces[$ace->getIndex()] = $ace;
        }
        return $this;
    }

    /**
     * Adds to the entity access control list entries.
     *
     * @param mixed $objectAce
     * @return $this
     */
    public function addObjectAce($objectAce)
    {
        return $this->addObjectAces([$objectAce]);
    }

    /**
     * Sets the default object aces.
     *
     * @return $this
     */
    public abstract function initObjectAces();
}