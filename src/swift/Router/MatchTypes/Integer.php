<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\MatchTypes;


use Swift\HttpFoundation\RequestInterface;
use Swift\Router\Exceptions\UnexpectedValueException;

/**
 * Class Integer
 * @package Swift\Router\MatchTypes
 *
 * Match an integer
 */
class Integer implements MatchTypeInterface {

    public const IDENTIFIER = 'i';
    public const REGEX = '[0-9]++';

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
    public function parseValue( mixed $value, RequestInterface $request ): int {
        if (! $filtered = filter_var($value, FILTER_VALIDATE_INT)) {
            throw new UnexpectedValueException(sprintf('Expected integer got %s', $value));
        }

        return $filtered;
    }

}