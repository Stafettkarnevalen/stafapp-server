<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 10.22
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait UniqueNameTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait VersionedUniqueNameTrait
{
    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="name_fld", type="string", length=128, unique=true)
     * @Assert\NotBlank()
     * @var string $name The unique name
     */
    protected $name;

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}