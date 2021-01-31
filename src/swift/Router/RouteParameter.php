<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use Swift\Kernel\Attributes\DI;
use Swift\Router\MatchTypes\MatchTypeInterface;

/**
 * Class RouteParameter
 * @package Swift\Router
 */
#[DI(autowire: false)]
final class RouteParameter {

    /**
     * RouteParameter constructor.
     *
     * @param string $block
     * @param string $pre
     * @param MatchTypeInterface|string $type
     * @param string $param
     * @param string $optional
     * @param mixed|null $value
     */
    public function __construct(
        public string $block,
        public string $pre,
        public MatchTypeInterface|string $type,
        public string $param,
        public string $optional,
        public mixed $value = null,
    ) {
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->value ?? '';
    }
}