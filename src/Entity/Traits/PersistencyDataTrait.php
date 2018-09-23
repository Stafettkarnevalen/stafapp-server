<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 9.44
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serialize;
use JMS\Serializer\Annotation as Jms;

/**
 * Trait PersistencyDataTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait PersistencyDataTrait
{
    /**
     * @ORM\Column(name="id_fld", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serialize\Groups({"for_api"})
     * @Jms\Groups({"for_api"})
     * @Jms\Expose(true)
     * @var integer $id The id of the entity
     */
    protected $id;

    /**
     * @ORM\Column(name="created_at_fld", type="datetime", nullable=true)
     * @var \DateTime $createdAt The timestamp when the entity was created
     */
    protected $createdAt;

    /**
     * Gets the id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id. (Caution: use carefully)
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the created at date.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the created at date.
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        if (is_numeric($createdAt)) {
            $this->createdAt = new \DateTime();
            $this->createdAt = $this->createdAt->setTimestamp($createdAt);
        } else if (is_string($createdAt)) {
            $this->createdAt = new \DateTime($createdAt);
        } else {
            $this->createdAt = $createdAt;
        }
        return $this;
    }

    /**
     * Gets triggered only on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
    }
}