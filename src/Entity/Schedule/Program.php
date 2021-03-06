<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 11.05
 */
namespace App\Entity\Schedule;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Interfaces\ScheduledEvent;
use App\Entity\Interfaces\Serializable;
use App\Entity\Services\ServiceCategory;
use App\Entity\Traits\LoggableTrait;
use App\Entity\Traits\VersionedTitleAndTextTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\FieldsTrait;
use App\Entity\Traits\PersistencyDataTrait;

/**
 * @ORM\Table(name="program_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Event
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class Program extends ScheduledEntity implements Serializable, CreatedByUserInterface, LoggableEntity
{
    /** use created by user trait */
    use CreatedByUserTrait;

    /** use fields trait */
    use FieldsTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use name field */
    use VersionedTitleAndTextTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Services\ServiceCategory", inversedBy="programs")
     * @ORM\JoinColumn(name="service_category_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var ServiceCategory $serviceCategory The category of this program
     */
    protected $serviceCategory;

    /**
     * Gets the serviceCategory.
     *
     * @return ServiceCategory
     */
    public function getServiceCategory()
    {
        return $this->serviceCategory;
    }

    /**
     * Sets the serviceCategory.
     *
     * @param ServiceCategory $serviceCategory
     * @return $this
     */
    public function setServiceCategory($serviceCategory)
    {
        $this->serviceCategory = $serviceCategory;

        return $this;
    }

    /**
     * Gets a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * Gets all ScheduledEvents of this entity.
     *
     * @return ArrayCollection
     */
    public function getSchedule()
    {
        $events = new ArrayCollection();
        $events[$this->getStarts()->format('c')] = new ProgramSchedule($this, ScheduledEvent::EVENT_TYPE_STARTS);
        return $events;
    }
}