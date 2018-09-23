<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 3.29
 */

namespace App\Entity\Relays;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Traits\LoggableTrait;
use App\Entity\Traits\TitleAndTextTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\NotesTrait;
use App\Entity\Traits\PersistencyDataTrait;

/**
 * @ORM\Table(name="race_result_action_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Relays
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class RaceResultAction implements \Serializable, CreatedByUserInterface, LoggableEntity
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** use fields trait */
    use FieldsTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use title and text */
    use TitleAndTextTrait;

    /** Use notes field */
    use NotesTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @const TYPE_PROTEST A protest
     */
    const TYPE_PROTEST          = "PROTEST";

    /**
     * @const TYPE_REVIEW A review
     */
    const TYPE_REVIEW           = "REVIEW";

    /**
     * @const TYPE_DISQUALIFICATION A disqualification
     */
    const TYPE_DISQUALIFICATION = "DISQUALIFICATION";

    /**
     * @const TYPE_DISQUALIFICATION A disqualification
     */
    const TYPE_WARNING          = "WARNING";

    /**
     * @const STATUS_ Status is pending
     */
    const STATUS_PENDING   = "PENDING";

    /**
     * @const STATUS_ Status is approved
     */
    const STATUS_APPROVED  = "APPROVED";

    /**
     * @const STATUS_ Status is withdrawn
     */
    const STATUS_WITHDRAWN = "WITHDRAWN";

    /**
     * @const STATUS_ Status is dismissed
     */
    const STATUS_DISMISSED = "DISMISSED";

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="type_fld", type="string", columnDefinition="ENUM('PROTEST', 'REVIEW', 'DISQUALIFICATION', 'WARNING')", nullable=false)
     * @Assert\Choice(choices={"PROTEST", "REVIEW", "DISQUALIFICATION", "WARNING"}, multiple=false)
     * @var string $type The type of this action
     */
    private $type;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="status_fld", type="string", columnDefinition="ENUM('PENDING', 'APPROVED', 'WITHDRAWN', 'DISMISSED')", nullable=false)
     * @Assert\Choice(choices={"PENDING", "APPROVED", "WITHDRAWN", "DISMISSED"}, multiple=false)
     * @var string $status The status of this action
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="RaceResult", inversedBy="actions")
     * @ORM\JoinColumn(name="result_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var RaceResult $result The Result that this action belongs to
     */
    private $result;

    /**
     * @ORM\OneToMany(targetEntity="RaceResultAction", mappedBy="parent")
     * @var ArrayCollection $children children of this action
     */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="RaceResultAction", inversedBy="children")
     * @ORM\JoinColumn(name="parent_fld", referencedColumnName="id_fld", nullable=true)
     * @var RaceResultAction $parent The parent of this action
     */
    protected $parent;


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

    /**
     * Gets the result.
     *
     * @return RaceResult
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Sets the result.
     *
     * @param RaceResult $result
     * @return $this
     */
    public function setResult(RaceResult $result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Gets the Team.
     *
     * @return Team
     */
    public function getTeam()
    {
        if (!$this->result)
            return null;

        return $this->result->getTeam();
    }

    /**
     * Gets the children.
     *
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets the children.
     *
     * @param ArrayCollection $children
     * @return $this
     */
    public function setChildren(ArrayCollection $children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Gets the parent.
     *
     * @return RaceResultAction|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent.
     *
     * @param RaceResultAction|null $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }


}