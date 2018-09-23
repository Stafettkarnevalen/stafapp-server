<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 01/01/2018
 * Time: 2.37
 */

namespace App\Entity\Security;

use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\Serializable;
use App\Entity\Schools\SchoolUnit;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\LifespanTrait;
use App\Entity\Traits\PersistencyDataTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="school_manager_position_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity(repositoryClass="App\Repository\SchoolManagerPositionRepository")
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Security
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SchoolManagerPosition implements Serializable, CreatedByUserInterface
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** use fields trait */
    use FieldsTrait;

    /** Use lifespan fields */
    use LifespanTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @const TYPE_ASSIGNED A constant for assignments by administrators
     */
    const TYPE_ASSIGNED    = 'ASSIGNED';

    /**
     * @const TYPE_INVITATION A constant for invitations
     */
    const TYPE_INVITATION  = 'INVITATION';

    /**
     * @const TYPE_REQUEST A constant for requests
     */
    const TYPE_REQUEST     = 'REQUEST';

    /**
     * @const STATUS_ACCEPTED A constant for accepted invitations or requests
     */
    const STATUS_ACCEPTED  = 'ACCEPTED';

    /**
     * @const STATUS_DENIED A constant for denied requests or invitations
     */
    const STATUS_DENIED = 'DENIED';

    /**
     * @const STATUS_PENDING A constant for pending invitations or requests
     */
    const STATUS_PENDING   = 'PENDING';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schools\SchoolUnit", inversedBy="managerPositions")
     * @ORM\JoinColumn(name="school_unit_fld", referencedColumnName="id_fld", nullable=false)
     * @var SchoolUnit The school unit that this invitation or request concerns
     */
    protected $schoolUnit;

    /**
     * @ORM\ManyToOne(targetEntity="SchoolManager", inversedBy="positions")
     * @ORM\JoinColumn(name="manager_fld", referencedColumnName="id_fld", nullable=true)
     * @var SchoolManager The school manager that this invitation or request concerns
     */
    protected $manager;

    /**
     * @ORM\Column(name="username_fld", type="string", length=60, nullable=true)
     * @Assert\Email()
     * @var string The user's username
     */
    protected $username;

    /**
     * @ORM\Column(name="type_fld", type="string",
     *     columnDefinition="ENUM('ASSIGNED', 'INVITATION', 'REQUEST')",
     *     options={"default": "ASSIGNED"}, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"ASSIGNED", "INVITATION", "REQUEST"})
     * @var string $type The type, invitation or request
     */
    protected $type;

    /**
     * @ORM\Column(name="status_fld", type="string",
     *     columnDefinition="ENUM('ACCEPTED', 'DENIED', 'PENDING')",
     *     options={"default": "ACCEPTED"}, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice({"ACCEPTED", "DENIED", "PENDING"})
     * @var string $status The status of the invitation or request
     */
    protected $status;

    /**
     * SchoolManagerPosition constructor.
     */
    public function __construct()
    {
        $this->type = self::TYPE_ASSIGNED;
        $this->status = self::STATUS_ACCEPTED;
        $this->isActive = true;
        $this->from = new \DateTime();
    }

    /**
     * Gets the schoolUnit.
     *
     * @return SchoolUnit
     */
    public function getSchoolUnit()
    {
        return $this->schoolUnit;
    }

    /**
     * Sets the schoolUnit.
     *
     * @param SchoolUnit $schoolUnit
     * @return $this
     */
    public function setSchoolUnit(SchoolUnit $schoolUnit)
    {
        $this->schoolUnit = $schoolUnit;

        return $this;
    }

    /**
     * Gets the manager.
     *
     * @return SchoolManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Sets the manager.
     *
     * @param SchoolManager $manager
     * @return $this
     */
    public function setManager(SchoolManager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Gets the username.
     *
     * @return string
     */
    public function getUsername()
    {
        if ($this->manager)
            return $this->manager->getUsername();
        return $this->username;
    }

    /**
     * Sets the username.
     *
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Gets the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the status.
     *
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getName()
    {
        if ($this->manager)
            return $this->manager->getFullname();
        return $this->username;
    }
}