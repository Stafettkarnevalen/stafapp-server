<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 11.01
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait SchoolClassSpanTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait SchoolClassSpanTrait
{
    /**
     * @ORM\Column(name="min_class_of_fld", type="integer", nullable=false)
     * @Assert\NotBlank()
     * @var integer $minClassOf The start of the class span, the low class
     */
    protected $minClassOf;

    /**
     * @ORM\Column(name="max_class_of_fld", type="integer", nullable=false)
     * @Assert\NotBlank()
     * @var integer $maxClassOf The end of the class span, the high class
     */
    protected $maxClassOf;

    /**
     * Gets the minClassOf.
     *
     * @return mixed
     */
    public function getMinClassOf()
    {
        return $this->minClassOf;
    }

    /**
     * Sets the minClassOf.
     *
     * @param mixed $minClassOf
     * @return $this
     */
    public function setMinClassOf($minClassOf)
    {
        $this->minClassOf = $minClassOf;

        return $this;
    }

    /**
     * Gets the maxClassOf.
     *
     * @return mixed
     */
    public function getMaxClassOf()
    {
        return $this->maxClassOf;
    }

    /**
     * Sets the maxClassOf.
     *
     * @param mixed $maxClassOf
     * @return $this
     */
    public function setMaxClassOf($maxClassOf)
    {
        $this->maxClassOf = $maxClassOf;

        return $this;
    }


}