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
 * Raised when a user has performed an operation that should be considered
 * suspicious from a security perspective.
 */
#[DI( exclude: true, autowire: false )]
class SuspiciousOperationException extends \UnexpectedValueException implements RequestExceptionInterface
{
}
