<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 10.22
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait AbbreviationTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait VersionedAbbreviationTrait
{
    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="abbreviation_fld", type="string", length=24, nullable=true)
     * @var string $abbreviation An abbreviation of a name
     */
    protected $abbreviation;

    /**
     * Gets the abbreviation.
     *
     * @return string|null
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * Sets the abbreviation.
     *
     * @param string|null $abbreviation
     * @return $this
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }
}