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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait VersionedTitleTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait VersionedTitleTrait
{
    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="title_fld", type="string", length=128, nullable=false)
     * @Assert\NotBlank()
     * @var string $title The versioned title
     */
    protected $title;

    /**
     * Gets the title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title
     * @return $this;
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}