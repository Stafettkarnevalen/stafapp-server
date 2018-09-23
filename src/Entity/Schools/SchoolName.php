<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 13/12/2016
 * Time: 9.45
 */

namespace App\Entity\Schools;


use App\Entity\Interfaces\ChronologicalEntityInterface;
use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\Serializable;
use App\Entity\Traits\LoggableTrait;
use App\Entity\Traits\VersionedAbbreviationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\VersionedAddressTrait;
use App\Entity\Traits\CloneableTrait;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\VersionedLifespanTrait;
use App\Entity\Traits\VersionedNameTrait;
use App\Entity\Traits\PersistencyDataTrait;
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="school_name_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Schools
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SchoolName implements Serializable, CreatedByUserInterface, LoggableEntity, ChronologicalEntityInterface
{
    /**
     * Use data asscociated with addresses
     * @Serialize\Groups({"school_api"})
     * @Jms\Groups({"school_api"})
     * @Jms\Expose(true)
     */
    use VersionedAddressTrait;

    /** Use clone functions */
    use CloneableTrait;

    /**
     * use created by user trait
     * @Serialize\Groups({"school_api"})
     * @Jms\Groups({"school_api"})
     * @Jms\Expose(true)
     */
    use CreatedByUserTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /**
     * Use abbreviation trait
     * @Serialize\Groups({"school_api"})
     * @Jms\Groups({"school_api"})
     * @Jms\Expose(true)
     */
    use VersionedAbbreviationTrait;

    /**
     * Use lifespan fields
     * @Serialize\Groups({"school_api"})
     * @Jms\Groups({"school_api"})
     * @Jms\Expose(true)
     */
    use VersionedLifespanTrait;

    /**
     * Use name field
     * @Serialize\Groups({"school_api"})
     * @Jms\Groups({"school_api"})
     * @Jms\Expose(true)
     */
    use VersionedNameTrait;

    /**
     * Use persistency data such as id and timestamps
     * @Serialize\Groups({"school_api"})
     * @Jms\Groups({"school_api"})
     * @Jms\Expose(true)
     */
    use PersistencyDataTrait;

    /**
     * @Gedmo\Slug(fields={"name"}, style="lower", separator=".", unique=true)
     * @ORM\Column(name="email_slug_fld", type="string", length=64, nullable=false)
     * @Serialize\Groups({"school_api"})
     * @Jms\Groups({"school_api"})
     * @Jms\Expose(true)
     */
    protected $emailSlug;

    /**
     * @ORM\ManyToOne(targetEntity="School", inversedBy="names")
     * @ORM\JoinColumn(name="school_fld", referencedColumnName="id_fld", nullable=false)
     * @Serialize\Groups({"school_api"})
     * @Serialize\MaxDepth(1)
     * @Jms\Groups({"school_api"})
     * @Jms\MaxDepth(1)
     * @Jms\Expose(true)
     */
    protected $school;

    /**
     * SchoolName constructor.
     */
    public function __construct()
    {
        $this->from = new \DateTime("now");
        $this->isActive = true;
    }

    /**
     * Gets the school name as a slug used in emails
     * @return mixed
     */
    public function getEmailSlug()
    {
        return $this->emailSlug;
    }

    /**
     * Gets school
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
     *
     * @return SchoolName
     */
    public function setSchool($school)
    {
        $this->school = $school;

        return $this;
    }

    /**
     * Gets fields that fill should skip
     *
     * @return array
     */
    public function getSkipFill()
    {
        return ['id', 'created', 'modified', 'emailSlug', 'skip'];
    }

    /**
     * Gets the siblings of this name.
     *
     * @param ObjectManager|null $em
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getSiblings(ObjectManager $em = null)
    {
        $siblings = $this->getSchool()->getNames();
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['from' => 'ASC']);
        return $siblings->matching($criteria);
    }

    /**
     * Gets the predecessors.
     * @param ObjectManager|null $em
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getPredecessors(ObjectManager $em = null)
    {
        $siblings = $this->getSiblings();

        $criteria = Criteria::create()
            ->where(Criteria::expr()->lte('until', $this->getFrom()))
            ->andWhere(Criteria::expr()->neq('until', null))
            ->orderBy(['from' => 'ASC']);
        return $siblings->matching($criteria);
    }

    /**
     * Gets the successors.
     *
     * @param ObjectManager|null $em
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getSuccessors(ObjectManager $em = null)
    {
        if ($this->getUntil() == null)
            return new ArrayCollection();

        $siblings = $this->getSiblings();
        $criteria = Criteria::create()
            ->where(Criteria::expr()->gte('from', $this->getUntil()))
            ->orderBy(['from' => 'ASC']);
        return $siblings->matching($criteria);
    }

    /**
     * Gets a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }
}