<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Exceptions;



use Swift\DependencyInjection\Attributes\DI;

/**
 * Class InternalErrorException
 * @package Swift\Router\Exceptions
 */
#[DI(exclude: true)]
class InternalErrorException extends \Swift\HttpFoundation\Exception\InternalErrorException {

    protected $code = 500;

}