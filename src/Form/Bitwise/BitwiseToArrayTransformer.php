<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/10/2017
 * Time: 11.36
 */

namespace App\Form\Bitwise;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class BitwiseToArrayTransformer
 * @package App\Form\Bitwise
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class BitwiseToArrayTransformer implements DataTransformerInterface
{
    /** @var int $bits the number of bits */
    private $bits = 32;

    /**
     * BitwiseToArrayTransformer constructor.
     *
     * @param $bits
     */
    public function __construct($bits)
    {
        $this->bits = $bits;
    }

    /**
     * Transforms the value.
     *
     * @param integer $value
     * @return array|null
     */
    public function transform($value)
    {
        if ($value == null)
            return null;

        $bits = [];
        for ($i = 0; $i < $this->bits; $i++)
            $bits['bit_' . $i] = ($value & 1 << $i) ? true : false;
        return $bits;
    }

    /**
     * Reverse transforms the value.
     *
     * @param array $value
     * @return null|integer
     */
    public function reverseTransform($value)
    {
        if ($value === null || !is_array($value))
            return null;

        $mask = 0;
        for ($i = 0; $i < $this->bits; $i++)
            if ($value['bit_' . $i])
                $mask |= 1 << $i;
        return $mask;
    }
}