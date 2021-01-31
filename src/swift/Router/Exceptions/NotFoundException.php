<?php declare(strict_types=1);

namespace Swift\Router\Exceptions;

use RuntimeException;
use Swift\Kernel\Attributes\DI;

/**
 * Class NotFoundException
 * @package Swift\Router\Exceptions
 */
#[DI(exclude: true)]
class NotFoundException extends \Swift\HttpFoundation\Exception\NotFoundException {

    protected $code = 404;

}