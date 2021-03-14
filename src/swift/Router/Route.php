<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use JetBrains\PhpStorm\Pure;
use Swift\HttpFoundation\RequestInterface;
use Swift\HttpFoundation\RequestMatcher;
use Swift\Kernel\Attributes\DI;
use Swift\Router\MatchTypes\MatchTypeInterface;
use Swift\Security\Authorization\AuthorizationTypesEnum;

/**
 * Class Route
 * @package Swift\Router
 */
#[DI( exclude: true )]
class Route implements RouteInterface {

    public const TAG_LOGIN = 'login';

    /** @var RequestInterface $request */
    private RequestInterface $request;

    /** @var RouteParameter[] $params */
    private array $params = array();

    /** @var MatchTypeInterface[] */
    private array $matchTypes = array();

    /**
     * Route constructor.
     *
     * @param string|null $name
     * @param string $regex The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param array $methods HTTP methods on which the route applies. Methods must be one or more of 5 HTTP Methods (GET|POST|PATCH|PUT|DELETE)
     * @param string $controller FQCN of the controller class
     * @param string|null $action
     * @param array $authType
     * @param array $isGranted
     * @param array $tags
     * @param RouteInterface|null $controllerRoute Controller route params used as globals and/or prefix
     */
    #[Pure]
    public function __construct(
        private string|null $name,
        private string $regex,
        private array $methods,
        private string $controller,
        private string|null $action,
        private array $authType,
        private array $isGranted,
        private array $tags = array(),
        private RouteInterface|null $controllerRoute = null,
    ) {
    }

    /**
     * Validate whether given route matches the passed request
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function matchesRequest( RequestInterface $request ): bool {
        // Method did not match, continue to next route.
        if ( ! $this->methodApplies( $request->getMethod() ) ) {
            return false;
        }

        $requestUrl         = $request->getUri()->getPath();
        $lastRequestUrlChar = $requestUrl[ strlen( $requestUrl ) - 1 ];

        if ( $this->getFullPath() === '*' ) {
            // * wildcard (matches all)
            return true;
        }

        if ( isset( $this->getFullPath()[0] ) && $this->getFullPath()[0] === '@' ) {
            // @ regex delimiter
            $pattern      = '`' . substr( $this->getFullPath(), 1 ) . '`u';
            $match        = preg_match( $pattern, $requestUrl, $this->params ) === 1;
            $this->params = Utils::formatRouteParams( $this->params );

            return $match;
        }

        if ( ( $position = strpos( $this->getFullPath(), '[' ) ) === false ) {
            // No params in url, do string comparison
            return strcmp( trim( $requestUrl, '/' ), trim( $this->getFullPath(), '/' ) ) === 0;
        }

        // Compare longest non-param string with url before moving on to regex
        // Check if last character before param is a slash, because it could be optional if param is optional too (see https://github.com/dannyvankooten/AltoRouter/issues/241)
        if ( strncmp( $requestUrl, $this->getFullPath(), $position ) !== 0 && ( $lastRequestUrlChar === '/' || $this->getFullPath()[ $position - 1 ] !== '/' ) ) {
            return false;
        }

        $matcher = new RequestMatcher( $this->getFullRegex(), null, $this->getMethods() );
        if ( $matcher->matches( $request ) ) {
            $this->params = $this->updateParams($matcher->getParams());

            return true;
        }

        return false;
    }

    /**
     * Checks whether a given HTTPMethod applies for this route
     *
     * @param string $method
     *
     * @return bool
     */
    #[Pure]
    public function methodApplies( string $method ): bool {
        return in_array( $method, $this->methods, false );
    }

    /**
     * Get full route path including controller base
     *
     * @return string|null
     */
    #[Pure]
    public function getFullPath(): ?string {
        if ( is_null( $this->regex ) ) {
            return null;
        }

        return is_null( $this->controllerRoute ) ? $this->regex : $this->controllerRoute->getRegex() . '/' . $this->regex;
    }

    /**
     * Get full route regex including controller base
     *
     * @return string|null
     */
    public function getFullRegex(): ?string {
        $route      = $this->getFullPath();

        foreach ( $this->getParamsFromPath( true ) as $parameter ) {
            $type = $parameter->getType();

            if ( $parameter->getType() instanceof MatchTypeInterface ) {
                $type = $type->getRegex();
            }


            $pre = $parameter->getPre();
            if ( $parameter->getPre() === '.' ) {
                $pre = '\.';
            }

            $optional = $parameter->getOptional() !== '' ? '?' : null;
            $param    = $parameter->getParam();

            //Older versions of PCRE require the 'P' in (?P<named>)
            $pattern = '(?:'
                       . ( $pre !== '' ? $pre : null )
                       . '('
                       . ( $param !== '' ? "?P<$param>" : null )
                       . $type
                       . ')'
                       . $optional
                       . ')'
                       . $optional;

            $route = str_replace( $parameter->getBlock(), $pattern, $route );
        }

        return "`^$route$`u";
    }

    /**
     * @param bool $fresh
     *
     * @return RouteParameter[]
     */
    public function getParamsFromPath( bool $fresh = false ): array {
        if ( $fresh || !isset($this->params) ) {
            $this->params = Utils::getRouteParametersFromPath( $this->getFullPath(), $this->matchTypes );
        }

        return $this->params;
    }

    /**
     * Update params with value
     *
     * @param array $params
     *
     * @return RouteParameter[]
     */
    private function updateParams( array $params ): array {
        foreach (Utils::formatRouteParams( $params ) as $name => $value) {
            if (array_key_exists($name, $this->params)) {
                $this->params[$name]->setValue($this->params[$name]->getType()->parseValue($value, $this->request));
            }
        }

        return $this->params;
    }

    /**
     * @return RouteParameter[]
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * @param RouteParameter[] $params
     */
    public function setParams( array $params ): void {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getMethods(): array {
        return $this->methods;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName( ?string $name ): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getRegex(): string {
        return $this->regex;
    }

    /**
     * @param string $regex
     */
    public function setRegex( string $regex ): void {
        $this->regex = $regex;
    }

    /**
     * @param array $methods
     */
    public function setMethods( array $methods ): void {
        $this->methods = $methods;
    }

    /**
     * @return string
     */
    public function getController(): string {
        return $this->controller;
    }

    /**
     * @param string $controller
     */
    public function setController( string $controller ): void {
        $this->controller = $controller;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string {
        return $this->action;
    }

    /**
     * @param string|null $action
     */
    public function setAction( ?string $action ): void {
        $this->action = $action;
    }

    /**
     * @return bool
     */
    public function isAuthRequired(): bool {
        return !in_array(AuthorizationTypesEnum::PUBLIC_ACCESS, $this->authType, true);
    }

    /**
     * @return array
     */
    public function getAuthType(): array {
        return $this->authType;
    }

    /**
     * @param array $authType
     */
    public function setAuthType( array $authType ): void {
        $this->authType = $authType;
    }

    /**
     * @param MatchTypeInterface[] $matchTypes
     */
    public function setMatchTypes( array $matchTypes ): void {
        $this->matchTypes = $matchTypes;
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest( RequestInterface $request ): void {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function setIsGranted( array $isGranted ): void {
        $this->isGranted = $isGranted;
    }

    /**
     * @inheritDoc
     */
    public function getIsGranted(): array {
        return $this->isGranted;
    }

    /**
     * @param RouteInterface $route
     */
    public function setControllerRoute( RouteInterface $route ): void {
        $this->controllerRoute = $route;
    }

    /**
     * @return RouteInterface|null
     */
    public function getControllerRoute(): ?RouteInterface {
        return $this->controllerRoute ?? null;
    }

    /**
     * @return array
     */
    public function getTags(): array {
        return $this->tags;
    }


}