<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 01/12/13
 * Time: 12:06
 */

namespace Uco\ConsignaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class PathExists
 * @package Uco\ConsignaBundle\Validator\Constraints
 * @Annotation
 */
class PathExists extends Constraint
{
    public $dont_exists_message = 'La ruta "%string%" no existe';

    public $not_absolute_path_message = 'La ruta "%string%" no es absoluta';
}