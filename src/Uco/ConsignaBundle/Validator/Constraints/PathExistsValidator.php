<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 01/12/13
 * Time: 12:09
 */

namespace Uco\ConsignaBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PathExistsValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param PathExists $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value != realpath($value)) {
            $this->context->addViolation($constraint->not_absolute_path_message, array('%string%' => $value));
        }
        if (false === is_dir($value)) {
            $this->context->addViolation($constraint->dont_exists_message, array('%string%' => $value));
        }
    }

} 