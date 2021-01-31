<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use Psr\Http\Message\RequestInterface;
use Swift\Router\MatchTypes\MatchTypeInterface;

/**
 * Interface RouteInterface
 * @package Swift\Router
 */
interface RouteInterface {

    /**
     * Get full route path including controller base
     *
     * @return string|null
     */
    public function getFullPath(): ?string;

    /**
     * Get full route regex including controller base
     *
     * @param array $matchTypes
     *
     * @return string|null
     */
    public function getFullRegex(array $matchTypes = array()): ?string;

    /**
     * Checks whether a given HTTPMethod applies for this route
     *
     * @param string $method
     *
     * @return bool
     */
    public function methodApplies( string $method): bool;

    /**
     * Validate whether given route matches the passed request
     *
     * @param RequestInterface $request
     * @param MatchTypeInterface[] $matchTypes
     *
     * @return bool
     */
    public function matchesRequest( RequestInterface $request, array $matchTypes = array() ): bool;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string|null $name
     */
    public function setName( ?string $name ): void;

    /**
     * @return string
     */
    public function getRegex(): string;

    /**
     * @param string $regex
     */
    public function setRegex( string $regex ): void;

    /**
     * @return string|null
     */
    public function getControllerBase(): ?string;

    /**
     * @param string|null $controllerBase
     */
    public function setControllerBase( ?string $controllerBase ): void;

    /**
     * @return array
     */
    public function getMethods(): array;

    /**
     * @param array $methods
     */
    public function setMethods( array $methods ): void;

    /**
     * @return string
     */
    public function getController(): string;

    /**
     * @param string $controller
     */
    public function setController( string $controller ): void;

    /**
     * @return string|null
     */
    public function getAction(): ?string;

    /**
     * @param string|null $action
     */
    public function setAction( ?string $action ): void;

    /**
     * @return RouteParameter[]
     */
    public function getParams(): array;

    /**
     * @param RouteParameter[] $params
     */
    public function setParams( array $params ): void;

    /**
     * @return bool
     */
    public function isAuthRequired(): bool;

    /**
     * @param bool $authRequired
     */
    public function setAuthRequired( bool $authRequired ): void;

    /**
     * @return array
     */
    public function getAuthLevels(): array;

    /**
     * @param array $authLevels
     */
    public function setAuthLevels( array $authLevels ): void;

}