<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/09/2017
 * Time: 15.01
 */

namespace App\Entity\Communication;

use App\Entity\Interfaces\MessageDistributionInterface;
use App\Entity\Relays\Race;
use App\Entity\Schools\School;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class RaceDistribution
 * @package App\Entity\Communication
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class RaceDistribution implements MessageDistributionInterface
{
    /**
     * @var Race $race The race that serves as a distribution.
     *                 All managers of teams in this race will get the message.
     */
    protected $race;

    /**
     * RaceDistribution constructor.
     * @param Race $race
     */
    public function __construct(Race $race)
    {
        $this->race = $race;
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
        foreach ($this->race->getSchools() as $school) {
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
        return $this->race->getRelay()->getName();
    }

    /**
     * Gets the Entity class behing this distribution.
     * @return string
     */
    public function getClass()
    {
        return Race::class;
    }

    /**
     * Gets the id of the Entity behind this distribution.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->race->getId();
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