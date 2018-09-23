<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 01/01/2018
 * Time: 2.37
 */

namespace App\Entity\Security;

use App\Entity\Schools\School;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SchoolAdministrator extends User
{
    /**
     * SchoolAdmin constructor.
     *
     * @param School $school
     */
    public function __construct(School $school)
    {
        parent::__construct();
        $this
            ->setPlainPassword($school->getNumber())
            ->setLocale('sv')
            ->setIsActive(true)
            ->update($school);
    }

    /**
     * Updates the school admin.
     *
     * @param School $school
     * @return $this
     */
    public function update(School $school)
    {
        $this
            ->setUsername('rektor.' . $school->getNumber() . '@stafapp.stafettkarnevalen.fi');

        if ($school->getName()) {
            $this
                ->setFirstname('Administratör')
                ->setLastname($school->getName()->getName())
                ->setEmail($school->getName()->getEmail())
            ;
        } else {
            $this
                ->setFirstname('Skolans')
                ->setLastname('Administratör')
                ->setEmail('noreply@stafapp.stafettkarnevalen.fi')
            ;
        }
        if ($group = $school->getGroup())
            $this->addGroup($group);
        return $this;
    }

    /**
     * Gets the SchoolAdministrator's UserTicket.
     *
     * @return UserTicket|null
     */
    public function getPrincipalTicket()
    {
        $tickets = $this->getTickets();
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $this))
            ->andWhere(Criteria::expr()->eq('type', UserTicket::TYPE_USB))
            ->andWhere(Criteria::expr()->eq('isActive', true))
        ;
        $tickets = $tickets->matching($criteria);
        if ($tickets->count() === 1)
            return $tickets->offsetGet(0);
        return null;
    }

    /**
     * Gets the SchoolAdministrator's School.
     *
     * @return School|null
     */
    public function getSchool()
    {
        /** @var UserTicket $ticket */
        $ticket = $this->getPrincipalTicket();
        if ($ticket) {
            return $ticket->getSchool();
        }
        return null;
    }

    /*public function jsonSerialize()
    {
        return [
            self::class => $this->getFields(["groups", "tickets", "schools", "logEvents", "profile"])
        ];
    }
    */

    /**
     * Gets an email address to use with google groups.
     *
     * @return string
     */
    public function getGoogleEmail()
    {
        return ($this->getEmail() ? $this->getEmail() : $this->getUsername());
    }
}