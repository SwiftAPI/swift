<?php declare(strict_types=1);

namespace Swift\HttpFoundation\Exception;

use RuntimeException;
use Swift\HttpFoundation\Response;
use Swift\Kernel\Attributes\DI;

/**
 * Class NotFoundException
 * @package Swift\Router\Exceptions
 */
#[DI(exclude: true)]
class NotFoundException extends RuntimeException {

    protected $code = 404;

}