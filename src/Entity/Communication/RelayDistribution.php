<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/09/2017
 * Time: 15.01
 */

namespace App\Entity\Communication;


use App\Entity\Interfaces\MessageDistributionInterface;
use App\Entity\Relays\Relay;
use App\Entity\Schools\School;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class RelayDistribution
 * @package App\Entity\Communication
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class RelayDistribution implements MessageDistributionInterface
{
    /**
     * @var Relay The relay of which the latest race serves as a distribution.
     *            All managers of teams in this race will get the message.
     */
    protected $relay;

    /**
     * RelayDistribution constructor.
     * @param Relay $relay
     */
    public function __construct(Relay $relay)
    {
        $this->relay = $relay;
    }

    /**
     * Gets the actual users that get this message.
     *
     * @return ArrayCollection
     */
    public function getUsers()
    {
        $users = new ArrayCollection([]);
        /** @var School $school */
        foreach ($this->relay->getLatestRace()->getSchools() as $school) {
            foreach ($school->getManagers() as $user) {
                if (!$users->contains($user))
                    $users->add($user);
            }
        }
        return $users;
    }

    /**
     * Gets a label for this distribution.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->relay->getName();
    }

    /**
     * Gets the Entity class behing this distribution.
     * @return string
     */
    public function getClass()
    {
        return Relay::class;
    }

    /**
     * Gets the id of the Entity behind this distribution.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->relay->getId();
    }

    /**
     * Gets the class and id combination of the Entity behind this distribution.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->getClass() . '-' . $this->getId();
    }
}