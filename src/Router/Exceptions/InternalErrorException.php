<?php declare(strict_types=1);

namespace Swift\Router\Exceptions;

use RuntimeException;
use Swift\Kernel\Attributes\DI;

/**
 * Class InternalErrorException
 * @package Swift\Router\Exceptions
 */
#[DI(exclude: true)]
class InternalErrorException extends \Swift\HttpFoundation\Exception\InternalErrorException {

    protected $code = 500;

}