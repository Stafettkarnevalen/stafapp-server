<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 13.25
 */

namespace App\Entity\Invoicing;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\OrderedEntityInterface;
use App\Entity\Interfaces\Serializable;
use App\Entity\Traits\ActiveTrait;
use App\Entity\Traits\LoggableTrait;
use App\Entity\Traits\VersionedNameTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\DataTrait;
use App\Entity\Traits\VersionedOrderedEntityTrait;
use App\Entity\Traits\PersistencyDataTrait;

/**
 * @ORM\Table(name="bank_account_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity(repositoryClass="App\Repository\BankAccountRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Invoicing
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class BankAccount implements Serializable, CreatedByUserInterface, LoggableEntity, OrderedEntityInterface
{
    /** use active */
    use ActiveTrait;

    /** use created by user trait */
    use CreatedByUserTrait;

    /** Use data attributes */
    use DataTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use ordered entity trait */
    use VersionedOrderedEntityTrait;

    /** Use name */
    use VersionedNameTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="iban_fld", type="string", length=35, nullable=false)
     * @Assert\Length(min=12, max=34, minMessage="length.minimum {{ limit }}", maxMessage="length.maximum {{ limit }}")
     * @Assert\NotBlank()
     * @var string $iban The International Bank Account Number IBAN.
     */
    protected $iban;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="bic_fld", type="string", length=12, nullable=false)
     * @Assert\Length(min=8, max=11, minMessage="length.minimum {{ limit }}", maxMessage="length.maximum {{ limit }}")
     * @Assert\NotBlank()
     * @var string $bic The Bank Identification Code BIC
     */
    protected $bic;

    /**
     * @ORM\ManyToOne(targetEntity="InvoiceAddress", inversedBy="bankAccounts")
     * @ORM\JoinColumn(name="invoice_address_fld", referencedColumnName="id_fld", nullable=false)
     * @var InvoiceAddress $address The invoice address that this account belongs to
     */
    protected $address;

    /**
     * BankAccount constructor.
     */
    public function __construct()
    {
        $this->setIsActive(true);
    }

    /**
     * Gets the iban.
     *
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * Sets the iban.
     *
     * @param string $iban
     * @return $this
     */
    public function setIban(string $iban)
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * Gets the bic.
     *
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Sets the bic.
     *
     * @param string $bic
     * @return $this
     */
    public function setBic(string $bic)
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * Gets the address.
     *
     * @return InvoiceAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the address.
     *
     * @param InvoiceAddress $address
     * @return $this
     */
    public function setAddress(InvoiceAddress $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Gets the siblings of this ordered entity.
     *
     * @param ObjectManager $em
     * @return ArrayCollection
     */
    public function getSiblings(ObjectManager $em = null)
    {
        $accounts = $this->address->getBankAccounts();
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $accounts->matching($criteria);
    }

}