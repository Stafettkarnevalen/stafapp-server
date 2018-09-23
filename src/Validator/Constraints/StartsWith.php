<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 05/09/2017
 * Time: 15.52
 */

namespace App\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

class StartsWith  extends Constraint
{
    public $message = 'does.not.start.with';

    private $value = null;
    private $ignoreCase = false;

    public function __construct($value = null, $ignoreCase = false)
    {
        parent::__construct();

        $this->value = $value;
        $this->ignoreCase = $ignoreCase;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getIgnoreCase()
    {
        return $this->ignoreCase;
    }
}