<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 15/12/2016
 * Time: 15.09
 */

namespace App\Entity\Schools;

use App\Entity\Communication\Message;
use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\RequiresGroupInterface;
use App\Entity\Interfaces\Serializable;
use App\Entity\Relays\Relay;
use App\Entity\Security\Group;
use App\Entity\Security\SchoolManager;
use App\Entity\Security\SchoolManagerPosition;
use App\Entity\Services\Service;
use App\Entity\Traits\CloneableTrait;
use App\Entity\Traits\ContainsMessageTrait;
use App\Entity\Traits\LoggableTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Relays\Competitor;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\VersionedActiveTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="school_unit_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity(repositoryClass="App\Repository\SchoolUnitRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Schools
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SchoolUnit implements Serializable, CreatedByUserInterface, LoggableEntity, RequiresGroupInterface
{
    /** use sctive trait */
    use VersionedActiveTrait;

    /** use created by user trait */
    use CreatedByUserTrait;

    /** Use is cloneable trait */
    use CloneableTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /** Can contain a message */
    use ContainsMessageTrait;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="password_fld", type="integer", columnDefinition="INT(8) UNSIGNED ZEROFILL", nullable=false)
     * @Assert\NotBlank()
     */
    protected $password;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\Group", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="group_fld", referencedColumnName="id_fld", nullable=true)
     */
    protected $group;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Security\SchoolManagerPosition", mappedBy="schoolUnit", cascade={"persist", "remove", "merge"})
     */
    protected $managerPositions;

    /**
     * @ORM\ManyToOne(targetEntity="School", inversedBy="schoolUnits")
     * @ORM\JoinColumn(name="school_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     */
    protected $school;

    /**
     * @ORM\OneToMany(targetEntity="SchoolUnitHasType", mappedBy="schoolUnit", cascade={"persist" ,"merge", "remove"})
     * @ORM\OrderBy({"season" = "DESC"})
     * @var ArrayCollection $hasSchoolTypes The school types that this unit has at a specified season
     */
    protected $hasSchoolTypes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invoicing\InvoiceAddress", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     * @var ArrayCollection $addresses The school units invoice addresses
     */
    protected $addresses;

    /**
     * @ORM\OneToMany(targetEntity="ServiceInvoiceAddress", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     * @var ArrayCollection $serviceInvoiceAddresses The school units invoice addresses per service type
     */
    protected $serviceInvoiceAddresses;

    /**
     * @ORM\OneToMany(targetEntity="SchoolUnitName", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     */
    protected $names;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Relays\Competitor", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     */
    protected $competitors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Relays\Team", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     */
    protected $teams;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Services\Order", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     */
    protected $orders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Services\ContactPerson", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     */
    protected $contactPersons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Cheerleading\Cheerleader", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     */
    protected $cheerleaders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Cheerleading\CheerleadingSquad", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     */
    protected $cheerleadingSquads;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Events\EventParticipant", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     */
    protected $eventParticipants;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Events\EventSquad", mappedBy="schoolUnit", cascade={"persist", "merge", "remove"})
     */
    protected $eventSquads;

    /**
     * The current SchoolUnitName
     * @var SchoolUnitName
     */
    protected $name;

    /**
     * SchoolUnit constructor.
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->serviceInvoiceAddresses = new ArrayCollection();
        $this->competitors = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->contactPersons = new ArrayCollection();
        $this->names = new ArrayCollection();
        $this->managerPositions = new ArrayCollection();
        $this->hasSchoolTypes = new ArrayCollection();

        $this->name = new SchoolUnitName();
        $this->names->add($this->name);
        $this->name->setSchoolUnit($this);
    }

    /**
     * Gets the hasSchoolTypes
     *
     * @return ArrayCollection
     */
    public function getHasSchoolTypes()
    {
        return $this->hasSchoolTypes;
    }

    /**
     * Sets the hasSchoolTypes
     *
     * @param ArrayCollection $hasSchoolTypes
     * @return $this
     */
    public function setHasSchoolTypes(ArrayCollection $hasSchoolTypes)
    {
        $this->hasSchoolTypes = $hasSchoolTypes;

        return $this;
    }

    /**
     * Gets the managers of this unit.
     *
     * @return ArrayCollection
     */
    public function getManagers()
    {
        $managers = new ArrayCollection();
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', SchoolManagerPosition::STATUS_ACCEPTED))
            ->andWhere(Criteria::expr()->eq('schoolUnit', $this))
            ->andWhere(Criteria::expr()->eq('isActive', true))
        ;

        foreach ($this->managerPositions->matching($criteria) as $mp)
            $managers->add($mp);
        return $managers;
    }

    /**
     * Sets the managers of this unit.
     *
     * @param ArrayCollection $managers
     * @return $this
     */
    public function setManagers(ArrayCollection $managers)
    {
        $managerPositions = new ArrayCollection();
        foreach ($managers as $manager) {
            $mp = new SchoolManagerPosition();
            $mp->setSchoolUnit($this)->setManager($manager);
            $managerPositions->add($mp);
        }

        return $this->setManagerPositions($managerPositions);
    }

    /**
     * Adds a manager
     *
     * @param SchoolManager $manager
     * @param bool $cascade
     *
     * @return $this
     */
    public function addManager(SchoolManager $manager, $cascade = true)
    {
        if ($this->hasManager($manager)) {
            return $this;
        }

        $mp = new SchoolManagerPosition();
        $mp->setManager($manager);
        $mp->setSchoolUnit($this);

        $this->managerPositions->add($mp);

        if ($cascade) $manager->addSchoolUnit($this, false);
        return $this;
    }

    /**
     * Removes a manager
     *
     * @param SchoolManager $manager
     * @param bool $cascade
     *
     * @return $this
     */
    public function removeManager(SchoolManager $manager, $cascade = true)
    {
        if (!$this->hasManager($manager)) {
            return $this;
        }
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', SchoolManagerPosition::STATUS_ACCEPTED))
            ->andWhere(Criteria::expr()->eq('schoolUnit', $this))
            ->andWhere(Criteria::expr()->eq('manager', $manager));

        $mps = $this->managerPositions->matching($criteria);
        if ($mps->count() !== 1)
            return $this;
        $mp = $mps->first();
        $this->managerPositions->removeElement($mp);

        if ($cascade) $manager->removeSchoolUnit($this, false);
        return $this;
    }

    /**
     * Checks if role has manager
     *
     * @param SchoolManager $manager
     *
     * @return bool
     */
    public function hasManager(SchoolManager $manager)
    {
        if (!$manager instanceof SchoolManager) {
            return false;
        }
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', SchoolManagerPosition::STATUS_ACCEPTED))
            ->andWhere(Criteria::expr()->eq('schoolUnit', $this))
            ->andWhere(Criteria::expr()->eq('manager', $manager))
        ;

        return ($this->managerPositions->matching($criteria)->count() == 1);
    }

    /**
     * Gets manager positions
     *
     * @return ArrayCollection
     */
    public function getManagerPositions()
    {
        return $this->managerPositions;
    }

    /**
     * Sets managers
     *
     * @param ArrayCollection $managerPositions
     * @return $this
     */
    public function setManagerPositions(ArrayCollection $managerPositions)
    {
        $this->managerPositions = $managerPositions;

        return $this;
    }

    /**
     * Gets the school
     *
     * @return School
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * Sets the school
     *
     * @param mixed $school
     * @return $this
     */
    public function setSchool($school)
    {
        $this->school = $school;

        return $this;
    }

    /**
     * Gets the school type
     *
     * @param integer|null $season
     * @return SchoolType
     */
    public function getSchoolType($season = null)
    {
        if ($season === null)
            $season = strftime('Y');

        $criteria = Criteria::create()->where(Criteria::expr()->eq("season", $season));

        $hasTypes = $this->hasSchoolTypes->matching($criteria);

        return ($hasTypes->count() === 1 ? $hasTypes->first() : null);
    }

    /**
     * Sets the school type
     *
     * @param integer|null $season
     * @param mixed $schoolType
     * @return $this
     */
    public function setSchoolType($schoolType, $season = null)
    {
        if ($season === null)
            $season = strftime('Y');

        $criteria = Criteria::create()->where(Criteria::expr()->eq("season", $season));

        $hasTypes = $this->hasSchoolTypes->matching($criteria);

        if ($hasTypes->count() === 1) {
            $hasTypes->first()->setSchoolType($schoolType);
        }

        return $this;
    }

    /**
     * Gets the addresses.
     *
     * @return ArrayCollection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Sets the addresses.
     *
     * @param ArrayCollection $addresses
     * @return $this
     */
    public function setAddresses(ArrayCollection $addresses)
    {
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * Gets the serviceInvoiceAddresses.
     *
     * @return ArrayCollection
     */
    public function getServiceInvoiceAddresses()
    {
        return $this->serviceInvoiceAddresses;
    }

    /**
     * Sets the serviceInvoiceAddresses.
     *
     * @param mixed $addresses
     * @return $this
     */
    public function setServiceInvoiceAddresses($addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * Gets the Teams.
     *
     * @param Service $service
     * @param Relay $relay
     * @return ArrayCollection
     */
    public function getTeams(Service $service = null, Relay $relay = null)
    {
        if (func_num_args() > 0) {
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->eq("schoolUnit", $this));
            if ($service)
                $criteria->andWhere(Criteria::expr()->in("race", $service->getRaces()));
            if ($relay)
                $criteria->andWhere(Criteria::expr()->in("race", $relay->getRaces()));

            return $this->teams->matching($criteria);
        }
        return $this->teams;
    }

    /**
     * Sets the Teams.
     *
     * @param mixed $teams
     * @return $this
     */
    public function setTeams($teams)
    {
        $this->teams = $teams;

        return $this;
    }

    /**
     * Gets the Competitors.
     *
     * @param mixed $season
     * @return ArrayCollection
     */
    public function getCompetitors($season = null)
    {
        if (func_num_args() == 1) {
            $competitors = new ArrayCollection();
            foreach ($this->competitors as $competitor) {
                if ($competitor->getSeason() == $season)
                    $competitors->add($competitor);
            }
            return $competitors;
        }
        return $this->competitors;
    }

    /**
     * Sets the Competitors.
     *
     * @param mixed $competitors
     * @return $this
     */
    public function setCompetitors($competitors)
    {
        $this->competitors = $competitors;

        return $this;
    }

    /**
     * Adds a Competitor.
     *
     * @param Competitor $competitor
     * @return $this
     */
    public function addCompetitor(Competitor $competitor)
    {
        $this->competitors->add($competitor);

        return $this;
    }

    /**
     * Gets the Orders.
     *
     * @param mixed $season
     * @return ArrayCollection
     */
    public function getOrders($season = null)
    {
        if (func_num_args() == 1) {
            $orders = new ArrayCollection();
            foreach ($this->orders as $order) {
                if ($order->getSeason() == $season)
                    $orders->add($order);
            }
            return $orders;
        }
        return $this->orders;
    }

    /**
     * Sets the Orders.
     *
     * @param mixed $orders
     * @return $this
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;

        return $this;
    }

    /**
     * Gets the ContactPersons
     *
     * @param mixed $season
     * @return mixed
     */
    public function getContactPersons($season = null)
    {
        if (func_num_args() == 1) {
            $persons = new ArrayCollection();
            foreach ($this->contactPersons as $person) {
                if ($person->getSeason() == $season)
                    $persons->add($person);
            }
            return $persons;
        }
        return $this->contactPersons;
    }

    /**
     * Sets the ContactPersons
     *
     * @param mixed $contactPersons
     * @return $this
     */
    public function setContactPersons($contactPersons)
    {
        $this->contactPersons = $contactPersons;

        return $this;
    }

    /**
     * Gets the CheerleadingSquads.
     *
     * @return ArrayCollection
     */
    public function getCheerleadingSquads()
    {
        return $this->cheerleadingSquads;
    }

    /**
     * Sets the CheerleadingSquads.
     *
     * @param mixed $cheerleadingSquads
     * @return $this
     */
    public function setCheerleadingSquads($cheerleadingSquads)
    {
        $this->cheerleadingSquads = $cheerleadingSquads;

        return $this;
    }

    /**
     * Gets the EventSquads.
     *
     * @return ArrayCollection
     */
    public function getEventSquads()
    {
        return $this->eventSquads;
    }

    /**
     * Sets the EventSquads.
     *
     * @param mixed $eventSquads
     * @return $this
     */
    public function setEventSquads($eventSquads)
    {
        $this->eventSquads = $eventSquads;
        return $this;
    }

    /**
     * Gets the cheerleaders.
     *
     * @return mixed
     */
    public function getCheerleaders()
    {
        return $this->cheerleaders;
    }

    /**
     * Sets the cheerleaders.
     *
     * @param mixed $cheerleaders
     * @return $this
     */
    public function setCheerleaders($cheerleaders)
    {
        $this->cheerleaders = $cheerleaders;

        return $this;
    }

    /**
     * Gets the eventParticipants.
     *
     * @return mixed
     */
    public function getEventParticipants()
    {
        return $this->eventParticipants;
    }

    /**
     * Sets the eventParticipants.
     *
     * @param mixed $eventParticipants
     * @return $this
     */
    public function setEventParticipants($eventParticipants)
    {
        $this->eventParticipants = $eventParticipants;

        return $this;
    }

    /**
     * Gets the password.
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the password.
     *
     * @param mixed $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Gets the names.
     *
     * @return ArrayCollection
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * Sets the names.
     *
     * @param mixed $names
     * @return $this
     */
    public function setNames($names)
    {
        $this->names = $names;

        return $this;
    }

    /**
     * Gets the name of the school according to the timestamp
     *
     * @param null $datetime
     *
     * @return SchoolUnitName
     */
    public function getName($datetime = null)
    {
        if (func_num_args() == 0 && $this->name !== null && $this->name->getIsActive()) {
            return $this->name;
        }
        if ($datetime === null) {
            $datetime = new \DateTime("now");
        }
        /** @var SchoolUnitName $name */
        foreach($this->names as $name) {
            if (($name->getFrom() < $datetime) &&
                ($name->getUntil() === null || $name->getUntil() > $datetime) &&
                $name->getIsActive()) {
                if (func_num_args() == 0)
                    $this->name = $name;
                return $name;
            }
        }
        return null;
    }

    /**
     * Sets the name.
     *
     * @param SchoolUnitName|null $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        if ($this->name && $this->name->getSchoolUnit() === null)
            $this->name->setSchoolUnit($this);
        if (!$this->getNames()->contains($name))
            $this->names->add($name);
        return $this;
    }

    /**
     * Creates a new name and ends the current name accordingly
     *
     * @param string $name
     * @param mixed $from
     * @param mixed $until
     * @param boolean $active
     * @param array $params
     *
     * @return SchoolUnitName
     */
    public function createName($name, $from, $until = null, $active = true, $params = [])
    {
        if ($current = $this->getName()) {
            $current->setUntil($from)->setIsActive(false);
            /** @var SchoolUnitName $suname */
            $suname = $current->cloneEntity()->fill($params);
            $suname->setSchoolUnit($this)->setFrom($from)->setUntil($until)->setIsActive($active);
        } else {
            $suname = new SchoolUnitName();
            $suname->fill($params);
            $suname->setSchoolUnit($this);
            $suname->setName($name)->setFrom($from)->setUntil($until)->setIsActive($active);
        }
        // $this->names->add($sname);

        return $suname;
    }

    /**
     * Adds a name
     *
     * @param SchoolUnitName $name
     *
     * @return $this
     */
    public function addName(SchoolUnitName $name)
    {
        if (!$this->names->contains($name)) {
            $this->names->add($name);
            if ($name->getSchoolUnit() !== $this)
                $name->setSchoolUnit($this);
        }

        return $this;
    }

    /**
     * Gets the group.
     *
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Sets the group.
     *
     * @param mixed $group
     * @return $this
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Gets the Group's name.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->getName()->getName();
    }

    /**
     * Gets the Group's email.
     *
     * @return string
     */
    public function getGroupEmail()
    {
        return $this->getName()->getEmailSlug() . '@stafapp.stafettkarnevalen.fi';
    }

    /**
     * Gets the Group's login route.
     *
     * @return string
     */
    public function getGroupLoginRoute()
    {
        return '/manager';
    }

    /**
     * Gets the Group's logout route.
     *
     * @return string
     */
    public function getGroupLogoutRoute()
    {
        return '/';
    }

    /**
     * Gets the Group's isGoogleSynced flag.
     *
     * @return boolean
     */
    public function getGroupIsGoogleSynced()
    {
        return true;
    }

    /** @var Message $message A message can be created at the time the user is persisted into the database */
    protected $message = null;

    /**
     * A message can be created at the time the user is persisted into the database.
     * @return Message
     */
    public function getMessage()
    {
        if ($this->message == null) {
            $this->message = new Message();
            $this->message->setType([Message::TYPE_INTERNAL]);
        }
        return $this->message;
    }

    /**
     * @ORM\PostLoad
     */
    public function onPostLoad()
    {
        $this->name = $this->names->first();
    }
}