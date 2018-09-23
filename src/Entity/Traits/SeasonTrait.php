<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 18.36
 */

namespace App\Entity\Traits;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait SeasonTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait SeasonTrait
{
    /**
     * @ORM\Column(name="season_fld", type="integer", columnDefinition="INT(11)", nullable=false)
     * @Assert\NotBlank()
     * @var integer $season The season (the year)
     */
    protected $season;

    /**
     * Gets the season.
     *
     * @return integer
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Sets the season.
     *
     * @param integer $season
     * @return $this
     */
    public function setSeason($season)
    {
        $this->season = $season;

        return $this;
    }
}