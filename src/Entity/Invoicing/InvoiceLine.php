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
use App\Entity\Traits\LoggableTrait;
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
 * @ORM\Table(name="invoice_line_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Invoicing
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class InvoiceLine implements Serializable, CreatedByUserInterface, LoggableEntity, OrderedEntityInterface
{
    const CALCULATE_FROM_AMOUNT = 1;
    const CALCULATE_FROM_APRICE_NO_VAT = 2;
    const CALCULATE_FROM_APRICE_VAT = 3;
    const CALCULATE_FROM_VAT_PERCENTAGE = 4;
    const CALCULATE_FROM_ADISCOUNT_NO_VAT = 5;
    const CALCULATE_FROM_ADISCOUNT_VAT = 6;
    const CALCULATE_FROM_DISCOUNT_PERCENTAGE = 7;

    /** use created by user trait */
    use CreatedByUserTrait;

    /** Use data attributes */
    use DataTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use ordered entity trait */
    use VersionedOrderedEntityTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="label_fld", type="string", length=128, nullable=false)
     * @Assert\NotBlank()
     * @var string $label The label of the line
     */
    protected $label;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="amount_fld", type="integer")
     * @Assert\NotBlank()
     * @var integer $amount The ordered amount
     */
    protected $amount;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="a_price_no_vat_fld", type="float", columnDefinition="DECIMAL(10,2)")
     * @Assert\NotBlank()
     * @var float $aPriceNoVat The price without VAT of one unit
     */
    protected $aPriceNoVat;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="a_price_vat_fld", type="float", columnDefinition="DECIMAL(10,2)")
     * @Assert\NotBlank()
     * @var float $aPriceVat The VAT part of the price of one unit
     */
    protected $aPriceVat;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="a_vat_percentage_fld", type="float", columnDefinition="DECIMAL(3,2)")
     * @Assert\NotBlank()
     * @var float $aVatPercentage The VAT percentage of the price
     */
    protected $aVatPercentage;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="a_discount_no_vat_fld", type="float", columnDefinition="DECIMAL(10,2)", nullable=true)
     * @var float $aDiscountNoVat The discount without VAT for one unit
     */
    protected $aDiscountNoVat;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="a_discount_vat_fld", type="float", columnDefinition="DECIMAL(10,2)", nullable=true)
     * @var float $aDiscountVat The VAT part for the discount for one unit
     */
    protected $aDiscountVat;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="a_discount_percentage_fld", type="float", columnDefinition="DECIMAL(3,2)", nullable=true)
     * @var float $aDiscountPercentage The discount percentage
     */
    protected $aDiscountPercentage;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="sum_no_vat_fld", type="float", columnDefinition="DECIMAL(10,2)")
     * @Assert\NotBlank()
     * @var float $sumNoVat The sum without VAT of the ordered units
     */
    protected $sumNoVat;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="sum_vat_fld", type="float", columnDefinition="DECIMAL(10,2)")
     * @Assert\NotBlank()
     * @var float $sumVat The VAT part of the sum of the ordered units
     */
    protected $sumVat;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="sum_discount_no_vat_fld", type="float", columnDefinition="DECIMAL(10,2)", nullable=true)
     * @var float $sumDiscountNoVat The sum without VAT of the discount for the ordered units
     */
    protected $sumDiscountNoVat;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="sum_discount_vat_fld", type="float", columnDefinition="DECIMAL(10,2)", nullable=true)
     * @var float $sumDiscountVat The VAT part of the discounted sum for the ordered units
     */
    protected $sumDiscountVat;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="sum_total_fld", type="float", columnDefinition="DECIMAL(10,2)")
     * @Assert\NotBlank()
     * @var float $sumTotal The sum to be paid, including VAT, after any discount
     */
    protected $sumTotal;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="invoiceLines")
     * @ORM\JoinColumn(name="invoice_fld", referencedColumnName="id_fld", nullable=true)
     * @var Invoice $invoice The invoice that this line belongs to
     */
    protected $invoice;

    /**
     * Gets the label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the label.
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Gets the amount.
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets the amount.
     *
     * @param int $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this->calculate(self::CALCULATE_FROM_AMOUNT);
    }

    /**
     * Gets the aPriceNoVat.
     *
     * @return float
     */
    public function getAPriceNoVat()
    {
        return $this->aPriceNoVat;
    }

    /**
     * Sets the aPriceNoVat.
     *
     * @param float $aPriceNoVat
     * @return $this
     */
    public function setAPriceNoVat($aPriceNoVat)
    {
        $this->aPriceNoVat = $aPriceNoVat;

        return $this->calculate(self::CALCULATE_FROM_APRICE_NO_VAT);
    }

    /**
     * Gets the aPriceVat.
     *
     * @return float
     */
    public function getAPriceVat()
    {
        return $this->aPriceVat;
    }

    /**
     * Sets the aPriceVat.
     *
     * @param float $aPriceVat
     * @return $this
     */
    public function setAPriceVat($aPriceVat)
    {
        $this->aPriceVat = $aPriceVat;

        return $this->calculate(self::CALCULATE_FROM_APRICE_VAT);
    }

    /**
     * Gets the aVatPercentage.
     *
     * @return float
     */
    public function getAVatPercentage()
    {
        return $this->aVatPercentage;
    }

    /**
     * Sets the aVatPercentage.
     *
     * @param float $aVatPercentage
     * @return $this
     */
    public function setAVatPercentage($aVatPercentage)
    {
        $this->aVatPercentage = $aVatPercentage;

        return $this->calculate(self::CALCULATE_FROM_VAT_PERCENTAGE);
    }

    /**
     * Gets the aDiscountNoVat.
     *
     * @return float
     */
    public function getADiscountNoVat()
    {
        return $this->aDiscountNoVat;
    }

    /**
     * Sets the aDiscountNoVat.
     *
     * @param float $aDiscountNoVat
     * @return $this
     */
    public function setADiscountNoVat($aDiscountNoVat)
    {
        $this->aDiscountNoVat = $aDiscountNoVat;

        return $this->calculate(self::CALCULATE_FROM_ADISCOUNT_NO_VAT);
    }

    /**
     * Gets the aDiscountVat.
     *
     * @return float
     */
    public function getADiscountVat()
    {
        return $this->aDiscountVat;
    }

    /**
     * Sets the aDiscountVat.
     *
     * @param float $aDiscountVat
     * @return $this
     */
    public function setADiscountVat($aDiscountVat)
    {
        $this->aDiscountVat = $aDiscountVat;

        return $this->calculate(self::CALCULATE_FROM_ADISCOUNT_VAT);
    }

    /**
     * Gets the aDiscountPercentage.
     *
     * @return float
     */
    public function getADiscountPercentage()
    {
        return $this->aDiscountPercentage;
    }

    /**
     * Sets the aDiscountPercentage.
     *
     * @param float $aDiscountPercentage
     * @return $this
     */
    public function setADiscountPercentage($aDiscountPercentage)
    {
        $this->aDiscountPercentage = $aDiscountPercentage;

        return $this->calculate(self::CALCULATE_FROM_DISCOUNT_PERCENTAGE);
    }

    /**
     * Gets the sumNoVat.
     *
     * @return float
     */
    public function getSumNoVat()
    {
        return $this->sumNoVat;
    }

    /**
     * Sets the sumNoVat.
     *
     * @param float $sumNoVat
     * @return $this
     */
    public function setSumNoVat($sumNoVat)
    {
        $this->sumNoVat = $sumNoVat;

        return $this;
    }

    /**
     * Gets the sumVat.
     *
     * @return float
     */
    public function getSumVat()
    {
        return $this->sumVat;
    }

    /**
     * Sets the sumVat.
     *
     * @param float $sumVat
     * @return $this
     */
    public function setSumVat($sumVat)
    {
        $this->sumVat = $sumVat;

        return $this;
    }

    /**
     * Gets the sumDiscountNoVat.
     *
     * @return float
     */
    public function getSumDiscountNoVat()
    {
        return $this->sumDiscountNoVat;
    }

    /**
     * Sets the sumDiscountNoVat.
     *
     * @param float $sumDiscountNoVat
     * @return $this
     */
    public function setSumDiscountNoVat($sumDiscountNoVat)
    {
        $this->sumDiscountNoVat = $sumDiscountNoVat;

        return $this;
    }

    /**
     * Gets the sumDiscountVat.
     *
     * @return float
     */
    public function getSumDiscountVat()
    {
        return $this->sumDiscountVat;
    }

    /**
     * Sets the sumDiscountVat.
     *
     * @param float $sumDiscountVat
     * @return $this
     */
    public function setSumDiscountVat($sumDiscountVat)
    {
        $this->sumDiscountVat = $sumDiscountVat;
        return $this;
    }

    /**
     * Gets the sumTotal.
     *
     * @return float
     */
    public function getSumTotal()
    {
        return $this->sumTotal;
    }

    /**
     * Sets the sumTotal.
     *
     * @param float $sumTotal
     * @return $this
     */
    public function setSumTotal($sumTotal)
    {
        $this->sumTotal = $sumTotal;
        return $this;
    }

    /**
     * Gets the invoice.
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Sets the invoice.
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
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
        $lines = $this->getInvoice()->getInvoiceLines();
        $criteria = Criteria::create()->where(Criteria::expr()->neq('id', $this->getId()))->orderBy(['order' => 'ASC']);
        return $lines->matching($criteria);
    }

    public function subtractAmount($amount)
    {
        $this->amount -= $amount;
        if ($this->amount <= 0) {
            $this->amount = 0;
        }

        return $this->calculate(self::CALCULATE_FROM_AMOUNT);
    }

    public function addAmount($amount)
    {
        $this->amount += $amount;

        return $this->calculate(self::CALCULATE_FROM_AMOUNT);
    }

    public function calculate($field)
    {
        switch ($field) {
            case self::CALCULATE_FROM_AMOUNT:
                /** calculate all fields using amount as variable */
                return $this
                    ->setSumNoVat($this->amount * $this->aPriceNoVat)
                    ->setSumDiscountNoVat( $this->amount * $this->aDiscountNoVat)
                    ->setSumVat($this->amount * $this->aPriceVat)
                    ->setSumDiscountVat($this->amount * $this->aDiscountVat)
                    ->setSumTotal($this->sumNoVat + $this->sumVat - ($this->sumDiscountNoVat + $this->sumDiscountVat));

            case self::CALCULATE_FROM_APRICE_NO_VAT:
                /** calculate vat price with base and percentage */
                if ($this->aVatPercentage) $this->aPriceVat = $this->aVatPercentage *  $this->aPriceNoVat / 100;

                /** calculate percentage with vat price and base  */
                else if ($this->aPriceVat && $this->aPriceNoVat) $this->aVatPercentage = $this->aPriceVat / $this->aPriceNoVat * 100;

                /** calculate discounts with percentage */
                if ($this->aDiscountPercentage) {
                    $this->aDiscountNoVat = $this->aDiscountPercentage * $this->aPriceNoVat / 100;
                    $this->aDiscountVat = $this->aDiscountPercentage * $this->aPriceVat / 100;
                }

                /** calculate percentage with base and discount */
                else if ($this->aDiscountNoVat && $this->aPriceNoVat) {
                    $this->aDiscountPercentage = $this->aDiscountNoVat / $this->aPriceNoVat * 100;
                    $this->aDiscountVat = $this->aDiscountPercentage * $this->aPriceVat / 100;
                }

                /** calculate percentage with vat price and vat discount */
                else if ($this->aDiscountVat && $this->aPriceVat) {
                    $this->aDiscountPercentage = $this->aDiscountVat / $this->aPriceVat * 100;
                    $this->aDiscountNoVat = $this->aDiscountPercentage * $this->aPriceNoVat / 100;
                }

                /** summarize */
                return $this
                    ->setSumNoVat($this->amount * $this->aPriceNoVat)
                    ->setSumDiscountNoVat( $this->amount * $this->aDiscountNoVat)
                    ->setSumVat($this->amount * $this->aPriceVat)
                    ->setSumDiscountVat($this->amount * $this->aDiscountVat)
                    ->setSumTotal($this->sumNoVat + $this->sumVat - ($this->sumDiscountNoVat + $this->sumDiscountVat));

            case self::CALCULATE_FROM_APRICE_VAT:
                /** calculate base with vat price and percentage */
                if ($this->aVatPercentage) $this->aPriceNoVat = $this->aPriceVat / $this->aVatPercentage * 100;

                /** calculate percentage with vat price and base  */
                else if ($this->aPriceVat && $this->aPriceNoVat) $this->aVatPercentage = $this->aPriceVat / $this->aPriceNoVat * 100;

                /** calculate discounts with percentage */
                if ($this->aDiscountPercentage) {
                    $this->aDiscountNoVat = $this->aDiscountPercentage * $this->aPriceNoVat / 100;
                    $this->aDiscountVat = $this->aDiscountPercentage * $this->aPriceVat / 100;
                }

                /** calculate percentage with base and discount */
                else if ($this->aDiscountNoVat && $this->aPriceNoVat) {
                    $this->aDiscountPercentage = $this->aDiscountNoVat / $this->aPriceNoVat * 100;
                    $this->aDiscountVat = $this->aDiscountPercentage * $this->aPriceVat / 100;
                }

                /** calculate percentage with vat price and vat discount */
                else if ($this->aDiscountVat && $this->aPriceVat) {
                    $this->aDiscountPercentage = $this->aDiscountVat / $this->aPriceVat * 100;
                    $this->aDiscountNoVat = $this->aDiscountPercentage * $this->aPriceNoVat / 100;
                }

                /** summarize */
                return $this
                    ->setSumNoVat($this->amount * $this->aPriceNoVat)
                    ->setSumDiscountNoVat( $this->amount * $this->aDiscountNoVat)
                    ->setSumVat($this->amount * $this->aPriceVat)
                    ->setSumDiscountVat($this->amount * $this->aDiscountVat)
                    ->setSumTotal($this->sumNoVat + $this->sumVat - ($this->sumDiscountNoVat + $this->sumDiscountVat));

            case self::CALCULATE_FROM_VAT_PERCENTAGE:
                /** calculate vat price from base and percentage */
                if ($this->aPriceNoVat) $this->aPriceVat = $this->aPriceNoVat * $this->aVatPercentage / 100;

                /** calculate base from vat price and percentage */
                else if ($this->aPriceVat && $this->aVatPercentage) $this->aPriceNoVat = $this->aPriceVat / $this->aVatPercentage * 100;

                /** calculate discounts with percentage */
                if ($this->aDiscountPercentage) {
                    $this->aDiscountNoVat = $this->aDiscountPercentage * $this->aPriceNoVat / 100;
                    $this->aDiscountVat = $this->aDiscountPercentage * $this->aPriceVat / 100;
                }

                /** calculate percentage with base and discount */
                else if ($this->aDiscountNoVat && $this->aPriceNoVat) {
                    $this->aDiscountPercentage = $this->aDiscountNoVat / $this->aPriceNoVat * 100;
                    $this->aDiscountVat = $this->aDiscountPercentage * $this->aPriceVat / 100;
                }

                /** calculate percentage with vat price and vat discount */
                else if ($this->aDiscountVat && $this->aPriceVat) {
                    $this->aDiscountPercentage = $this->aDiscountVat / $this->aPriceVat * 100;
                    $this->aDiscountNoVat = $this->aDiscountPercentage * $this->aPriceNoVat / 100;
                }

                /** summarize */
                return $this
                    ->setSumNoVat($this->amount * $this->aPriceNoVat)
                    ->setSumDiscountNoVat( $this->amount * $this->aDiscountNoVat)
                    ->setSumVat($this->amount * $this->aPriceVat)
                    ->setSumDiscountVat($this->amount * $this->aDiscountVat)
                    ->setSumTotal($this->sumNoVat + $this->sumVat - ($this->sumDiscountNoVat + $this->sumDiscountVat));

            case self::CALCULATE_FROM_ADISCOUNT_NO_VAT:
                /** calculate percentage from base and discount */
                if ($this->aPriceNoVat) $this->aDiscountPercentage = $this->aDiscountNoVat / $this->aPriceNoVat * 100;

                /** calculate base from percentage and discount */
                else if ($this->aDiscountNoVat && $this->aDiscountPercentage) $this->aPriceNoVat = $this->aDiscountNoVat / $this->aDiscountPercentage * 100;

                /** calculate vat price and discount with bases and percentage */
                if ($this->aVatPercentage) {
                    $this->aPriceVat = $this->aVatPercentage *  $this->aPriceNoVat / 100;
                    $this->aDiscountVat = $this->aVatPercentage *  $this->aDiscountNoVat / 100;
                }

                /** calculate percentage with vat price and base  */
                else if ($this->aDiscountVat && $this->aDiscountNoVat) $this->aVatPercentage = $this->aDiscountVat / $this->aDiscountNoVat * 100;

                return $this
                    ->setSumNoVat($this->amount * $this->aPriceNoVat)
                    ->setSumDiscountNoVat( $this->amount * $this->aDiscountNoVat)
                    ->setSumVat($this->amount * $this->aPriceVat)
                    ->setSumDiscountVat($this->amount * $this->aDiscountVat)
                    ->setSumTotal($this->sumNoVat + $this->sumVat - ($this->sumDiscountNoVat + $this->sumDiscountVat));

            case self::CALCULATE_FROM_ADISCOUNT_VAT:
                /** calculate percentage from vat and discount */
                if ($this->aPriceVat) $this->aDiscountPercentage = $this->aDiscountVat / $this->aPriceVat * 100;

                /** calculate vat from percentage and discount */
                else if ($this->aDiscountVat && $this->aDiscountPercentage) $this->aPriceVat = $this->aDiscountVat / $this->aDiscountPercentage * 100;

                /** calculate vat price and discount with bases and percentage */
                if ($this->aVatPercentage) {
                    $this->aPriceNoVat = $this->aPriceVat / $this->aVatPercentage * 100;
                    $this->aDiscountNoVat = $this->aDiscountVat / $this->aVatPercentage * 100;
                }

                /** calculate percentage with vat price and base  */
                else if ($this->aDiscountVat && $this->aDiscountNoVat) $this->aVatPercentage = $this->aDiscountVat / $this->aDiscountNoVat * 100;

                return $this
                    ->setSumNoVat($this->amount * $this->aPriceNoVat)
                    ->setSumDiscountNoVat( $this->amount * $this->aDiscountNoVat)
                    ->setSumVat($this->amount * $this->aPriceVat)
                    ->setSumDiscountVat($this->amount * $this->aDiscountVat)
                    ->setSumTotal($this->sumNoVat + $this->sumVat - ($this->sumDiscountNoVat + $this->sumDiscountVat));

            case self::CALCULATE_FROM_DISCOUNT_PERCENTAGE:

        }
        return $this;
    }

    public function finvoiceInvoiceRow(\SimpleXMLElement $parent)
    {
        $row = $parent->addChild('InvoiceRow');
        $row->addChild('ArticleIdentifier', $this->id);
        $row->addChild('ArticleName', html_entity_decode($this->label));
        $row->addChild('DeliveredQuantity', $this->amount)->addAttribute('QuantityUnitCode', 'st');
        $row->addChild('OrderedQuantity', $this->amount);
        $row->addChild('InvoicedQuantity', $this->sumTotal)->addAttribute('QuantityUnitCode', 'EUR');
        $row->addChild('UnitPriceAmount', $this->aPriceNoVat + $this->aPriceVat)->addAttribute('AmountCurrencyIdentifier', 'EUR');
        $row->addChild('RowPositionIdentifier', $this->order);
        $row->addChild('RowVatRatePercent', $this->aVatPercentage);
        $row->addChild('RowVatAmount', $this->sumVat)->addAttribute('AmountCurrencyIdentifier', 'EUR');
        $row->addChild('RowVatExcludedAmount', $this->sumNoVat)->addAttribute('AmountCurrencyIdentifier', 'EUR');

        return $row;
    }

}