<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 04/06/2017
 * Time: 14.20
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait PageModuleTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait PageModuleTrait
{
    /** Use Title trait */
    use TitleTrait;

    /** Use Data trait */
    use DataTrait;

    /** Use ordered entity */
    use OrderedEntityTrait;

    /**
     * @ORM\Column(name="page_fld", type="string", length=256, nullable=false)
     * @Assert\NotBlank()
     * @var string $page The page of the module
     */
    protected $page;

    /**
     * @ORM\Column(name="zone_fld", type="string", length=128, nullable=false)
     * @Assert\NotBlank()
     * @var string $zone The zone of the module
     */
    protected $zone;

    /**
     * Gets the page.
     *
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the page.
     *
     * @param string $page
     * @return $this;
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Gets the zone.
     *
     * @return string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Sets the zone.
     *
     * @param string $zone
     * @return $this
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }
}