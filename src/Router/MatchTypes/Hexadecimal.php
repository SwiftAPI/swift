<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\MatchTypes;

use Swift\HttpFoundation\RequestInterface;

/**
 * Class Hexadecimal
 * @package Swift\Router\MatchTypes
 */
class Hexadecimal implements MatchTypeInterface {

    public const IDENTIFIER = 'h';
    public const REGEX = '[0-9A-Fa-f]++';

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string {
        return static::IDENTIFIER;
    }

    /**
     * @inheritDoc
     */
    public function getRegex(): string {
        return static::REGEX;
    }

    /**
     * @inheritDoc
     */
    public function parseValue( mixed $value, RequestInterface $request ): mixed {
        return $value;
    }

}