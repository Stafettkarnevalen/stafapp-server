<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 9.59
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait ActiveTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait VersionedActiveTrait
{
    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="active_fld", type="boolean", nullable=true)
     * @var boolean $isActive A flag to determine if this Entity is active or not
     */
    protected $isActive;

    /**
     * Get isActive
     *
     * @return boolean|null
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isActive
     *
     * @param boolean|null $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }
}