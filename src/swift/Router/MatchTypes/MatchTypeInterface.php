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
use Swift\Kernel\Attributes\DI;
use Swift\Router\DiTags;
use Swift\Router\Exceptions\UnexpectedValueException;

/**
 * Interface MatchTypeInterface
 * @package Swift\Router\MatchTypes
 */
#[DI( tags: [DiTags::MATCH_TYPES], autowire: false )]
interface MatchTypeInterface {

    /**
     * Identifier which will be used to identify this match type
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Regex for this match type
     *
     * @return string
     */
    public function getRegex(): string;

    /**
     * Parse and/or filter value
     *
     * @param mixed $value
     * @param RequestInterface $request
     *
     * @return mixed
     *
     * @throws UnexpectedValueException When value is not as expected throw this error to result in a 400 error
     */
    public function parseValue(mixed $value, RequestInterface $request): mixed;

}