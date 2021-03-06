<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 16.27
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

/**
 * Trait PersonTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait PersonTrait
{
    /**
     * @ORM\Column(name="firstname_fld", type="string", length=64, options={"collation"="utf8_swedish_ci"})
     * @Assert\NotBlank()
     * @AppAssert\IsValidName()
     * @var string $firstname The first name or the given name
     */
    protected $firstname;

    /**
     * @ORM\Column(name="lastname_fld", type="string", length=64, options={"collation"="utf8_swedish_ci"})
     * @Assert\NotBlank()
     * @AppAssert\IsValidName()
     * @var string $lastname The last name or the surname
     */
    protected $lastname;

    /**
     * Get the first name.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set the first name.
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the last name.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set the last name.
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Gets the full name of the person
     *
     * @return string
     */
    public function getFullname()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * Gets the initials of the persons first and last name
     *
     * @return string
     */
    public function getInitials()
    {
        return strtoupper(substr($this->getFirstname(), 0, 1) . substr($this->getLastname(), 0, 1));
    }

    /**
     * Gets the name as Firstname L. where L is the first letter in the last name
     *
     * @return string
     */
    public function getFirstnameLDot()
    {
        return $this->getFirstname() . " " . strtoupper(substr($this->getLastname(), 0, 1)) . ".";
    }

    /**
     * Gets the name as F. Lastname where F is the first letter in the first name
     *
     * @return string
     */
    public function getFDotLastname()
    {
        return strtoupper(substr($this->getFirstname(), 0, 1)) . ". " . $this->getLastname();
    }

    public function getFDotLDot()
    {
        return strtoupper(substr($this->getFirstname(), 0, 1) . "." . substr($this->getLastname(), 0, 1) . ".");
    }

}