<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 10.18
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait AddressTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait AddressTrait
{
    /**
     * @ORM\Column(name="street_address_fld", type="string", length=64, nullable=false)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @var string $streetAddress The street address
     */
    protected $streetAddress;

    /**
     * @ORM\Column(name="zipcode_fld", type="integer", columnDefinition="INT(5) UNSIGNED ZEROFILL", nullable=false)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @var integer $zipcode The zip code
     */
    protected $zipcode;

    /**
     * @ORM\Column(name="city_fld", type="string", length=64, nullable=false)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @var string $city The city
     */
    protected $city;

    /**
     * @ORM\Column(name="pobox_fld", type="string", length=16, nullable=true)
     * @var string $pobox The PO box
     */
    protected $pobox;

    /**
     * @ORM\Column(name="country_fld", type="string", length=32, nullable=false)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @var string $country The country
     */
    protected $country;

    /**
     * @ORM\Column(name="email_fld", type="string", length=64, nullable=true)
     * @var string $email The email address
     */
    protected $email;

    /**
     * @ORM\Column(name="phone_fld", type="string", length=64, nullable=true)
     * @var string $phone The phone or cell number
     */
    protected $phone;

    /**
     * Gets the street address.
     *
     * @return string|null
     */
    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    /**
     * Sets the street address.
     *
     * @param string|null $streetAddress
     * @return $this
     */
    public function setStreetAddress($streetAddress)
    {
        $this->streetAddress = $streetAddress;

        return $this;
    }

    /**
     * Gets the zipcode.
     *
     * @return string
     */
    public function getZipcode()
    {
        return str_pad($this->zipcode, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Sets the zipcode.
     *
     * @param integer|string|null $zipcode
     * @return $this
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = str_pad($zipcode, 5, '0', STR_PAD_LEFT);

        return $this;
    }

    /**
     * Gets the city.
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the city.
     *
     * @param string|null $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Gets the pobox.
     *
     * @return string|null
     */
    public function getPobox()
    {
        return $this->pobox;
    }

    /**
     * Sets the pobox.
     *
     * @param string|null $pobox
     * @return $this
     */
    public function setPobox($pobox)
    {
        $this->pobox = $pobox;

        return $this;
    }

    /**
     * Gets the country.
     *
     * @return string|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Sets the country.
     *
     * @param string|null $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Gets the email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email.
     *
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets the phone.
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets the phone.
     *
     * @param string|null $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }
}