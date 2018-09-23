<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 03/06/2017
 * Time: 10.16
 */

namespace App\Entity\Traits;

/**
 * Trait HiddenFieldsTrait
 * @package App\Entity\Traits
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
trait HiddenFieldsTrait
{
    /**
     * @ORM\Column(name="hidden_fields_fld", type="array", nullable=true)
     * @var array $hiddenFields The fields that someone wants to remain hidden from the public
     */
    protected $hiddenFields;

    /**
     * Gets the names of the hidden fields.
     *
     * @return array
     */
    public function getHiddenFields()
    {
        return $this->hiddenFields;
    }

    /**
     * Sets the names of the hidden fields.
     *
     * @param array $hiddenFields
     * @return $this
     */
    public function setHiddenFields($hiddenFields)
    {
        $this->hiddenFields = $hiddenFields;

        return $this;
    }

}