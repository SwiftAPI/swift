<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\File\Exception;

use Swift\Kernel\Attributes\DI;

/**
 * Thrown when an error occurred in the component File.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
#[DI( exclude: true, autowire: false )]
class FileException extends \RuntimeException
{
}
