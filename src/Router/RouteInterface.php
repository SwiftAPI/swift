<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use Swift\HttpFoundation\RequestInterface;
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
     * @return string|null
     */
    public function getFullRegex(): ?string;

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
     *
     * @return bool
     */
    public function matchesRequest( RequestInterface $request ): bool;

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
     * @return RouteParameterBag
     */
    public function getParams(): RouteParameterBag;

    /**
     * @param RouteParameter[] $params
     */
    public function setParams( array $params ): void;

    /**
     * @return bool
     */
    public function isAuthRequired(): bool;

    /**
     * @return array
     */
    public function getAuthType(): array;

    /**
     * @param array $authType
     */
    public function setAuthType( array $authType ): void;

    /**
     * @param array $isGranted
     */
    public function setIsGranted( array $isGranted ): void;

    /**
     * @return array
     */
    public function getIsGranted(): array;

    /**
     * @return RouteTagBag
     */
    public function getTags(): RouteTagBag;

}