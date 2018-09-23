<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 12.58
 */

namespace App\Entity\Events;

use App\Entity\Interfaces\Serializable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Schools\School;
use App\Entity\Schools\SchoolUnit;
use App\Entity\Invoicing\InvoiceLine;
use App\Entity\Invoicing\Invoice;

/**
 * @ORM\Table(name="event_squad_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @package App\Entity\Event
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class EventSquad implements Serializable, CreatedByUserInterface
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** use fields trait */
    use FieldsTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @ORM\Column(name="bib_fld", type="integer", nullable=true)
     * @var integer $bib The bib number of the squad
     */
    protected $bib;

    /**
     * @ORM\Column(name="rank_fld", type="integer", nullable=true)
     * @var integer $rank The rank of the squad
     */
    protected $rank;

    /**
     * @ORM\Column(name="explanation_fld", type="text", nullable=true)
     * @var string $explanation An explanation for the rank
     */
    protected $explanation;

    /**
     * @ORM\ManyToOne(targetEntity="Competition", inversedBy="squads")
     * @ORM\JoinColumn(name="competition_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Competition $competition The competition that this squad is competing in
     */
    protected $competition;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schools\SchoolUnit", inversedBy="eventSquads")
     * @ORM\JoinColumn(name="school_unit_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var SchoolUnit $schoolUnit The school unit this squad belongs to
     */
    protected $schoolUnit;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoicing\InvoiceLine")
     * @ORM\JoinColumn(name="invoice_line_fld", referencedColumnName="id_fld", nullable=true)
     * @var InvoiceLine $invoiceLine The invoice line created by this squad
     */
    protected $invoiceLine;

    /**
     * @ORM\OneToMany(targetEntity="MemberOfEventSquad", mappedBy="squad", cascade={"persist", "remove"})
     * @var ArrayCollection $members The members in this squad
     */
    protected $members;

    /**
     * Gets the bib.
     *
     * @return integer
     */
    public function getBib()
    {
        return $this->bib;
    }

    /**
     * Sets the bib.
     *
     * @param integer $bib
     * @return $this
     */
    public function setBib($bib)
    {
        $this->bib = $bib;

        return $this;
    }

    /**
     * Gets the SchoolUnit.
     *
     * @return SchoolUnit
     */
    public function getSchoolUnit()
    {
        return $this->schoolUnit;
    }

    /**
     * Sets the SchoolUnit.
     *
     * @param SchoolUnit $schoolUnit
     * @return $this
     */
    public function setSchoolUnit($schoolUnit)
    {
        $this->schoolUnit = $schoolUnit;

        return $this;
    }

    /**
     * Gets the School.
     *
     * @return School
     */
    public function getSchool()
    {
        return $this->getSchoolUnit()->getSchool();
    }

    /**
     * Gets the InvoiceLine after Invoices have been issued.
     *
     * @return InvoiceLine
     */
    public function getInvoiceLine()
    {
        return $this->invoiceLine;
    }

    /**
     * Sets the InvoiceLine after Invoices have been issued.
     *
     * @param InvoiceLine $invoiceLine
     * @return $this
     */
    public function setInvoiceLine($invoiceLine)
    {
        $this->invoiceLine = $invoiceLine;

        return $this;
    }

    /**
     * Gets the Invoice containing this Team.
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->getInvoiceLine()->getInvoice();
    }

    /**
     * Gets the Members
     *
     * @return ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Sets the Members.
     *
     * @param ArrayCollection $members
     * @return $this
     */
    public function setMembers($members)
    {
        $this->members = $members;

        return $this;
    }

    /**
     * Gets the Competition.
     *
     * @return Competition
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * Sets the Competition.
     *
     * @param Competition $competition
     * @return $this
     */
    public function setCompetition($competition)
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * Gets the rank.
     *
     * @return integer|null
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Sets the rank.
     *
     * @param integer|null $rank
     * @return $this
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Gets the explanation.
     *
     * @return string|null
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Sets the explanation.
     *
     * @param string|null $explanation
     * @return $this
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
    }


    /**
     * Gets a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSchool()->getName();
    }
}