<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\MatchTypes;


use Psr\Http\Message\RequestInterface;

/**
 * Class Integer
 * @package Swift\Router\MatchTypes
 *
 * Match alphanumeric characters
 */
class AlphaNumeric implements MatchTypeInterface {

    public const IDENTIFIER = 'a';
    public const REGEX = '[0-9A-Za-z]++';

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
    public function parseValue( mixed $value, RequestInterface $request ): string {
        return preg_replace('/[^\da-z]/i', '', $value);
    }

}