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
 * Trait BusinessIdTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait VersionedBusinessIdTrait
{
    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="business_id_fld", type="string", length=10, nullable=true)
     * @var string $businessId A business id
     */
    protected $businessId;

    /**
     * Gets the businessId.
     *
     * @return string
     */
    public function getBusinessId()
    {
        return $this->businessId;
    }

    /**
     * Sets the businessId.
     *
     * @param string $businessId
     * @return $this
     */
    public function setBusinessId(string $businessId)
    {
        $this->businessId = $businessId;

        return $this;
    }


}