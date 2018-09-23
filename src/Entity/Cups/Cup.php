<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 17.35
 */

namespace App\Entity\Cups;

use App\Entity\Interfaces\LoggableEntity;
use App\Entity\Traits\LoggableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\Interfaces\CreatedByUserInterface;
use App\Entity\Traits\CloneableTrait;
use App\Entity\Traits\CreatedByUserTrait;
use App\Entity\Traits\NotesTrait;
use App\Entity\Traits\PersistencyDataTrait;
use App\Entity\Traits\VersionedPriceTrait;
use App\Entity\Services\Service;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="cup_table", options={"collate"="utf8_swedish_ci"})
 * @ORM\Entity
 * @UniqueEntity(fields={"sport","service"}, message="cup.exists")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @package App\Entity\Relays
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class Cup implements \Serializable, CreatedByUserInterface, LoggableEntity
{
    /** Use clone functions */
    use CloneableTrait;

    /** use created by user trait */
    use CreatedByUserTrait;

    /** Use loggable trait */
    use LoggableTrait;

    /** Use notes field */
    use NotesTrait;

    /** Use persistency data such as id and timestamps */
    use PersistencyDataTrait;

    /** Use price field */
    use VersionedPriceTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Sport", inversedBy="cups")
     * @ORM\JoinColumn(name="sport_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Sport $sport The Sport that this cup is based upon
     */
    protected $sport;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Services\Service", inversedBy="cups")
     * @ORM\JoinColumn(name="service_fld", referencedColumnName="id_fld", nullable=false)
     * @Assert\NotBlank()
     * @var Service $service The service type providing the season for this cup
     */
    protected $service;

    /**
     * Gets the sport.
     *
     * @return Sport
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Sets the sport.
     *
     * @param Sport $sport
     * @return $this
     */
    public function setSport($sport)
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * Gets the service.
     *
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Sets the service.
     *
     * @param Service $service
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Gets the season.
     *
     * @return integer
     */
    public function getSeason()
    {
        return $this->getService()->getSeason();
    }

    /**
     * Race constructor.
     */
    public function __construct()
    {

    }

}