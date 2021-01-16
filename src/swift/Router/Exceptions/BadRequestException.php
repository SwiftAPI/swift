<?php declare(strict_types=1);


namespace Swift\Router\Exceptions;

use RuntimeException;
use Swift\Kernel\Attributes\DI;

/**
 * Class BadRequestException
 * @package Swift\Router\Exceptions
 */
#[DI(exclude: true)]
class BadRequestException extends RuntimeException {

    protected $code = 400;

}