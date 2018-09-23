<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 25/11/2016
 * Time: 18.31
 */

namespace App\Validator\Constraints;

use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

class IsValidHashForUserValidator extends ConstraintValidator
{

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($constraint instanceof IsValidHashForUser) {
            $user = $constraint->getUser();
            $type = "get" . $constraint->getType();
            $builder = $constraint->getBuilder();
            $hash = $user->$type();

            /** @var ClickableInterface $button */
            $button = $builder->getForm()->get('resend');
            if ($button->isClicked())
                return;

            if ($hash !== $value) {
                $this->context->buildViolation($constraint->message . '.' . $constraint->getType())
                    ->addViolation();
            }
        }
    }
}