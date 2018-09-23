<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 26/11/2016
 * Time: 17.49
 */

namespace App\Form\PhoneNumber;

use App\Util\CSVReader;
use Symfony\Component\Form\DataTransformerInterface;

class PhoneNumberToArrayTransformer implements DataTransformerInterface
{
    /** @var null  */
    private $areaCodes = null;

    /**
     * @param mixed $value
     * @return array|null
     */
    public function transform($value)
    {
        if ($value === null)
            return null;

        if (!$this->areaCodes)
            $this->areaCodes = CSVReader::countryCodes();

        $countryCode = $value;

        while (!in_array($countryCode, $this->areaCodes) && strlen($countryCode)) {
            $countryCode = substr($countryCode, 0, -1);
        }

        if (!empty($countryCode)) {
            $number = str_replace([' ', '(', ')'], ['', '', ''], substr($value, strlen($countryCode)));
            if (!is_numeric($number))
                $number = null;
            return [
                'areaCode' => $countryCode,
                'number' => $number
            ];
        }
        return null;
    }

    /**
     * @param mixed $value
     * @return null|string
     */
    public function reverseTransform($value)
    {
        if ($value === null || empty($value['number']))
            return null;
        return implode('', $value);
    }
}