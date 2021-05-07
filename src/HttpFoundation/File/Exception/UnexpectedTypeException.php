<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\File\Exception;

class UnexpectedTypeException extends FileException
{
    public function __construct($value, string $expectedType)
    {
        parent::__construct(sprintf('Expected argument of type %s, %s given', $expectedType, get_debug_type($value)));
    }
}
