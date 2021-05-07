<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Exception;

use Swift\Kernel\Attributes\DI;

/**
 * The HTTP request contains headers with conflicting information.
 *
 * @author Magnus Nordlander <magnus@fervo.se>
 */
#[DI( exclude: true, autowire: false )]
class ConflictingHeadersException extends \UnexpectedValueException implements RequestExceptionInterface
{
}
