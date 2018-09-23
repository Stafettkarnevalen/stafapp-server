<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/09/2017
 * Time: 15.01
 */

namespace App\Entity\Communication;

use App\Entity\Interfaces\MessageDistributionInterface;
use App\Entity\Security\User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class UserDistribution
 * @package App\Entity\Communication
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class UserDistribution implements MessageDistributionInterface
{
    /** @var User The user that serves as the distribution. The user will get the message. */
    protected $user;

    /**
     * UserDistribution constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Gets the actual users that get this message.
     *
     * @return ArrayCollection
     */
    public function getUsers() {
        return new ArrayCollection([$this->user]);
    }

    /**
     * Gets a label for this distribution.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->user->getFullname();
    }

    /**
     * Gets the Entity class behing this distribution.
     * @return string
     */
    public function getClass()
    {
        return User::class;
    }

    /**
     * Gets the id of the Entity behind this distribution.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->user->getId();
    }

    /**
     * Returns a distribution of an array of UserDistributions.
     *
     * @param array $users
     * @return array
     */
    public static function all(array $users)
    {
        $all = [];
        foreach ($users as $user) {
            $all[] = new UserDistribution($user);
        }
        return $all;
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