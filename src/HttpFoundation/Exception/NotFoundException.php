<?php declare(strict_types=1);
/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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