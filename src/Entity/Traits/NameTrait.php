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
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;

/**
 * Trait NameTrait
 * @Jms\ExclusionPolicy(policy="NONE")
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait NameTrait
{
    /**
     * @ORM\Column(name="name_fld", type="string", length=64, nullable=false)
     * @Assert\NotBlank()
     * @Serialize\Groups({"for_api"})
     * @Jms\Groups({"for_api"})
     * @Jms\Expose(true)
     * @var string $name The name
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