<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 07/09/2017
 * Time: 19.32
 */

namespace App\Entity\Traits;

/**
 * Trait ApplyDateIntervalTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait ApplyDateIntervalTrait
{
    /**
     * Gets the fields that can be modified with a \DateInterval.
     *
     * @return array
     */
    public abstract function getDateIntervalApplicableFields();

    /**
     * Applies a \DateInterval to the applicable fields.
     *
     * @param \DateInterval $interval
     * @return $this
     */
    public function applyDateInterval(\DateInterval $interval)
    {
        /** @var \DateTime $field */
        foreach ($this->getDateIntervalApplicableFields() as $field) {
            if ($field === null)
                continue;
            $field->add($interval);
        }

        return $this;
    }

}