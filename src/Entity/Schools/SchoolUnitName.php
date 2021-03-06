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
use App\Entity\Traits\LoggableTrait;
use App\Entity\Traits\VersionedAbbreviationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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
 * @ORM\Table(name="school_unit_name_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Schools
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class SchoolUnitName implements \Serializable, CreatedByUserInterface, LoggableEntity, ChronologicalEntityInterface
{
    /** Use data asscociated with addresses */
    use VersionedAddressTrait;

    /** Use clone functions */
    use CloneableTrait;

    /** use created by user trait */
    use CreatedByUserTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use lifespan fields */
    use VersionedLifespanTrait;

    /** Use name field */
    use VersionedNameTrait;

    /** Use abbreviation trait */
    use VersionedAbbreviationTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @Gedmo\Slug(fields={"name"}, style="lower", separator=".", unique=true)
     * @ORM\Column(name="email_slug_fld", type="string", length=64, nullable=false)
     */
    protected $emailSlug;

    /**
     * @ORM\ManyToOne(targetEntity="SchoolUnit", inversedBy="names")
     * @ORM\JoinColumn(name="schoolunit_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     */
    protected $schoolUnit;

    /**
     * SchoolUnitName constructor.
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
     * Sets the emailSlug.
     *
     * @param mixed $emailSlug
     * @return $this
     */
    public function setEmailSlug($emailSlug)
    {
        $this->emailSlug = $emailSlug;

        return $this;
    }

    /**
     * Gets school unit
     *
     * @return SchoolUnit
     */
    public function getSchoolUnit()
    {
        return $this->schoolUnit;
    }

    /**
     * Sets the school unit
     *
     * @param mixed $schoolUnit
     *
     * @return $this
     */
    public function setSchoolUnit($schoolUnit)
    {
        $this->schoolUnit = $schoolUnit;

        return $this;
    }

    /**
     * Gets fields that fill should skip
     *
     * @return array
     */
    public function getSkipFill()
    {
        return ['id', 'created', 'modified', 'emailSlug'];
    }

    /**
     * Gets the siblings of this name.
     *
     * @param ObjectManager|null $em
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getSiblings(ObjectManager $em = null)
    {
        $siblings = $this->getSchoolUnit()->getNames();
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