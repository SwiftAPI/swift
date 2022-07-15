<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\MatchTypes;


use Psr\Http\Message\RequestInterface;
use Swift\DependencyInjection\Attributes\DI;

/**
 * Class MatchType
 * @package Swift\Router\MatchTypes
 */
#[DI(exclude: true)]
class MatchType implements MatchTypeInterface {

    /**
     * MatchType constructor.
     *
     * @param string $identifier
     * @param string $regex
     */
    public function __construct(
        private string $identifier,
        private string $regex,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string {
        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function getRegex(): string {
        return $this->regex;
    }

    /**
     * @inheritDoc
     */
    public function parseValue( mixed $value, RequestInterface $request ): string {
        return strip_tags($value);
    }
}