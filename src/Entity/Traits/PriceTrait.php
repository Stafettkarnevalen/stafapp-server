<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 17.41
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait PriceTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait PriceTrait
{
    /**
     * @ORM\Column(name="price_fld", type="float", columnDefinition="DECIMAL(4,2)", nullable=true)
     * @var float $price The price
     */
    protected $price;

    /**
     * Gets the price.
     *
     * @return float|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Sets the price.
     *
     * @param float|null $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }
}