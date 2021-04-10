<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Exception;

/**
 * Interface for Request exceptions.
 *
 * Exceptions implementing this interface should trigger an HTTP 400 response in the application code.
 */
interface RequestExceptionInterface
{
}
