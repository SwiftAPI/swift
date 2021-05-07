<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
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
        private string $block,
        private string $pre,
        private MatchTypeInterface|string $type,
        private string $param,
        private string $optional,
        private mixed $value = null,
    ) {
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->value ?? '';
    }

    /**
     * @return string
     */
    public function getBlock(): string {
        return $this->block;
    }

    /**
     * @return string
     */
    public function getPre(): string {
        return $this->pre;
    }

    /**
     * @return string|MatchTypeInterface
     */
    public function getType(): string|MatchTypeInterface {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getParam(): string {
        return $this->param;
    }

    /**
     * @return string
     */
    public function getOptional(): string {
        return $this->optional;
    }

    /**
     * @param mixed $value
     */
    public function setValue( mixed $value ): void {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed {
        return $this->value;
    }

}