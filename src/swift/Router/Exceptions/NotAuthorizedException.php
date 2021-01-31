<?php declare(strict_types=1);

namespace Swift\Router\Exceptions;

use RuntimeException;
use Swift\Kernel\Attributes\DI;

/**
 * Class NotAuthorizedException
 * @package Swift\Router\Exceptions
 */
#[DI(exclude: true)]
class NotAuthorizedException extends \Swift\HttpFoundation\Exception\NotAuthorizedException {

    protected $code = 401;

}