<?php
/**
 * Created by PhpStorm.
 * manager: rjurgens
 * Date: 13/12/2016
 * Time: 9.36
 */

namespace App\Entity\Schools;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\RequiresGroupInterface;
use App\Entity\Interfaces\Serializable;
use App\Entity\Security\SchoolAdministrator;
use App\Entity\Security\Group;
use App\Entity\Security\UserTicket;
use App\Entity\Traits\CloneableTrait;
use App\Entity\Traits\ContainsMessageTrait;
use App\Entity\Traits\LoggableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Security\User;
use App\Entity\Traits\ActiveTrait;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\NotesTrait;

/**
 * @ORM\Table(name="school_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity(repositoryClass="App\Repository\SchoolRepository")
 * @UniqueEntity(fields={"number"}, message="number.reserved")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @Jms\ExclusionPolicy("ALL")
 * @package App\Entity\Schools
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class School implements Serializable, CreatedByUserInterface, RequiresGroupInterface, LoggableEntity
{
    /**
     * Use is active flaga
     * @Serialize\Groups({"SchoolAPI", "Default"})
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\Expose(true)
     */
    use ActiveTrait;

    /**
     * Use created by user trait
     * @Serialize\Groups({"SchoolAPI", "Default"})
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\Expose(true)
     */
    use CreatedByUserTrait;

    /** Use cloneable traits */
    use CloneableTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /**
     * Use notes field
     * @Serialize\Groups({"SchoolAPI", "Default"})
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\Expose(true)
     */
    use NotesTrait;

    /**
     * Use persistency data such as id and timestamps
     * @Serialize\Groups({"SchoolAPI", "Default"})
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\Expose(true)
     */
    //use PersistencyDataTrait;

    /** Can contain a message */
    use ContainsMessageTrait;

    /**
     * @ORM\Column(name="id_fld", type="integer", columnDefinition="INT(5) UNSIGNED ZEROFILL", nullable=false)
     * @ORM\Id
     * @Assert\NotBlank()
     * @Serialize\Groups({"for_api"})
     * @Jms\Groups({"for_api"})
     * @Jms\Expose(true)
     * @var integer $id The id of the entity
     */
    protected $id;

    /**
     * @ORM\Column(name="password_fld", type="integer", columnDefinition="INT(8) UNSIGNED ZEROFILL", nullable=false)
     * @Assert\NotBlank()
     * @Serialize\Groups({"SchoolAPI", "Default"})
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\Expose(true)
     */
    protected $password;

    /**
     * @ORM\Column(name="created_at_fld", type="datetime", nullable=true)
     * @var \DateTime $createdAt The timestamp when the entity was created
     */
    protected $createdAt;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Group", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="group_fld", referencedColumnName="id_fld", nullable=true)
     * @Serialize\Groups({"SchoolAPI", "Default"})
     * @Serialize\MaxDepth(1)
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\MaxDepth(1)
     * @Jms\Expose(true)
     */
    protected $group;

    /**
     * @ORM\OneToMany(targetEntity="SchoolName", mappedBy="school", cascade={"persist", "merge", "remove"})
     * @Serialize\Groups({"SchoolAPI", "Default"})
     * @Serialize\MaxDepth(2)
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\MaxDepth(2)
     * @Jms\Expose(true)
     */
    protected $names;

    /**
     * @ORM\OneToMany(targetEntity="SchoolUnit", mappedBy="school", cascade={"persist", "merge", "remove"})
     * @Serialize\Groups({"SchoolAPI", "Default"})
     * @Serialize\MaxDepth(2)
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\MaxDepth(2)
     * @Jms\Expose(true)
     */
    protected $schoolUnits;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\UserTicket", inversedBy="school", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="principal_ticket_fld", referencedColumnName="id_fld", nullable=false)
     * @Serialize\Groups({"SchoolAPI", "Default"})
     * @Serialize\MaxDepth(1)
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\MaxDepth(1)
     * @Jms\Expose(true)
     */
    protected $principalTicket;

    /**
     * @var SchoolName $name
     * @Jms\Groups({"SchoolAPI", "Default"})
     * @Jms\MaxDepth(1)
     * @Jms\Expose(true)
     */
    protected $name;

    /**
     * Array of school types.
     * @var ArrayCollection
     */
    protected $schoolTypes;

    /**
     * Gets the id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id. (Caution: use carefully)
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @ORM\PostLoad
     */
    public function onPostLoad()
    {
        $units = $this->schoolUnits->toArray();
        usort($units, function(SchoolUnit $a, SchoolUnit $b)
        {
            return $a->getSchoolType()->getOrder() == $b->getSchoolType()->getOrder() ? 0 : ($a->getSchoolType()->getOrder() > $b->getSchoolType()->getOrder() ? -1 : 1);
        });
        $this->schoolUnits = new ArrayCollection($units);
        $this->name = $this->names->last();

        // return new ArrayCollection($units);
    }

    /**
     * Gets the created at date.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the created at date.
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        if (is_numeric($createdAt)) {
            $this->createdAt = new \DateTime();
            $this->createdAt = $this->createdAt->setTimestamp($createdAt);
        } else if (is_string($createdAt)) {
            $this->createdAt = new \DateTime($createdAt);
        } else {
            $this->createdAt = $createdAt;
        }
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
        $this->principalTicket = new UserTicket();
        $this->principalTicket
            ->setIsActive(true)
            ->setFor(UserTicket::FOR_LOGIN)
            ->setType(UserTicket::TYPE_USB)
            ->setTriesLeft(10000000)
            ->setPlaintextTicket($this->getPassword())
            ->setFrom(new \DateTime('now'))
            ->setUser(new SchoolAdministrator($this));
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreMerge()
    {
        $ticket = $this->getPrincipalTicket();
        $ticket
            ->setTicket(null)
            ->setPlaintextTicket($this->getPassword())
            ->setFrom(new \DateTime('now'));

        /** @var SchoolAdministrator $user */
        $user = $ticket->getUser();
        $user->update($this);
    }

    public function __construct()
    {
        $this->names = new ArrayCollection();
        $this->schoolTypes = new ArrayCollection();
        $this->schoolUnits = new ArrayCollection();
        $this->name = $this->names->count() ?
            $this->names->first() :
            (new SchoolName())->setSchool($this)->setIsActive(true)->setFrom(new \DateTime('now'));
    }

    /**
     * Gets the principalTicket.
     *
     * @return UserTicket
     */
    public function getPrincipalTicket()
    {
        return $this->principalTicket;
    }

    /**
     * Sets the principalTicket.
     *
     * @param UserTicket $principalTicket
     * @return $this
     */
    public function setPrincipalTicket($principalTicket)
    {
        $this->principalTicket = $principalTicket;

        return $this;
    }

    /**
     * Gets the principal user.
     *
     * @Jms\Expose(true)
     * @return User|null
     */
    public function getPrincipal()
    {
        $ticket = $this->getPrincipalTicket();
        return $ticket ? $ticket->getUser() : null;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->id = intval($number);
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return str_pad($this->password, 8, '0', STR_PAD_LEFT);
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return ArrayCollection
     */
    public function getNames()
    {
        $criteria = Criteria::create()->orderBy(['from' => 'DESC']);
        return $this->names->matching($criteria);
    }

    /**
     * @param ArrayCollection $names
     */
    public function setNames($names)
    {
        if ($this->name->getId() === null)
            $this->name = $names->first();
        $this->names = $names;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * Gets the name of the school according to the timestamp
     *
     * @param null $datetime
     *
     * @return SchoolName
     */
    public function getName($datetime = null)
    {
        if (func_num_args() == 0 && $this->name !== null && $this->name->getId() && $this->name->getIsActive()) {
            return $this->name;
        }
        if ($datetime === null) {
            $datetime = new \DateTime("now");
        }
        /** @var SchoolName $name */
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
     * @param SchoolName|null $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        if ($this->name && $this->name->getSchool() === null)
            $this->name->setSchool($this);
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
     * @return SchoolName
     */
    public function createName($name, $from, $until = null, $active = true, $params = [])
    {
        if ($current = $this->getName()) {
            $current->setUntil($from)->setIsActive(false);
            /** @var SchoolName $sname */
            $sname = $current->cloneEntity()->fill($params);
            $sname->setSchool($this)->setFrom($from)->setUntil($until)->setIsActive($active);
        } else {
            $sname = new SchoolName();
            $sname->fill($params);
            $sname->setSchool($this);
            $sname->setName($name)->setFrom($from)->setUntil($until)->setIsActive($active);
        }
        // $this->names->add($sname);

        return $sname;
    }

    /**
     * Adds a name
     *
     * @param SchoolName $name
     *
     * @return $this
     */
    public function addName(SchoolName $name)
    {
        if (!$this->names->contains($name)) {
            $this->names->add($name);
            if ($name->getSchool() !== $this)
                $name->setSchool($this);
        }

        return $this;
    }

    /**
     * Gets SchoolUnits
     * @return mixed
     */
    public function getSchoolUnits()
    {
        return $this->schoolUnits;
    }

    /**
     * Gets SchoolTypes
     * @return mixed
     */
    public function getSchoolTypes()
    {
        if (!$this->schoolTypes || $this->schoolTypes->count() == 0) {
            $this->schoolTypes = new ArrayCollection();
            foreach ($this->schoolUnits as $unit) {
                $this->schoolTypes->add($unit->getSchoolType());
            }
        }
        return $this->schoolTypes;
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

    /**
     * Gets all active managers in all active units.
     *
     * @return ArrayCollection
     */
    public function getManagers()
    {
        $managers = new ArrayCollection();
        /** @var SchoolUnit $unit */
        foreach ($this->getSchoolUnits() as $unit) {
            if ($unit->getIsActive()) {
                /** @var User $manager */
                foreach ($unit->getManagers() as $manager) {
                    if ($manager->getIsActive()) {
                        $managers->add($manager);
                    }
                }
            }
        }
        return $managers;
    }

    /**
     * @return array
     */
    public function getSkipFll()
    {
        return ['name', 'schoolTypes'];
    }

    /**
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize2()
    {
        $name = $this->getName();
        /** @var Group $group */
        $group = $this->getGroup();
        return [
            self::class => [
                'password' => $this->password,
                'isActive' => $this->isActive,
                'id' => $this->id,
                'createdAt' => $this->createdAt,
                'notes' => $this->notes,
                'name' => [
                    'name' => $name ? $name->getName() : null,
                    'id' => $name ? $name->getId() : null,
                ],
                'group' => [
                    'name' => $group ? $group->getEmail() : null,
                    'id' => $group ? $group->getId() : null,
                ],
            ],
        ];
    }
}