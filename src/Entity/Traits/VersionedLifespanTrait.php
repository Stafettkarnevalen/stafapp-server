<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 10.32
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trait LifespanTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait VersionedLifespanTrait
{
    /** Use is active flag */
    use ActiveTrait;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="from_fld", type="datetime")
     * @Assert\NotBlank()
     * @var \DateTime $from The time when the entity's lifesan begins
     */
    protected $from;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="until_fld", type="datetime", nullable=true)
     * @var \DateTime $until The time when the entity's lifespan ends
     */
    protected $until;

    /**
     * Gets from.
     *
     * @return \DateTime
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Sets from.
     *
     * @param mixed $from
     * @return $this
     */
    public function setFrom($from)
    {
        if (is_numeric($from)) {
            $this->from = new \DateTime();
            $this->from = $this->from->setTimestamp($from);
        } else if (is_string($from)) {
            $this->from = new \DateTime($from);
        } else {
            $this->from = $from;
        }
        return $this;
    }

    /**
     * Gets until.
     *
     * @return \DateTime
     */
    public function getUntil()
    {
        return $this->until;
    }

    /**
     * Sets until.
     *
     * @param mixed $until
     * @return $this
     */
    public function setUntil($until)
    {
        if (is_numeric($until)) {
            $this->until = new \DateTime();
            $this->until = $this->until->setTimestamp($until);
        } else if (is_string($until)) {
            $this->from = new \DateTime($until);
        } else {
            $this->until = $until;
        }

        return $this;
    }

    /**
     * Checks if this object is valid for the given timstamp.
     *
     * @param \DateTime $time
     * @return bool
     */
    public function isCurrent(\DateTime $time = null)
    {
        if (!$time)
            $time = new \DateTime('now');
        return (($this->getFrom() <= $time) && (($this->getUntil() == null) || ($this->getUntil() >= $time)));
    }
}