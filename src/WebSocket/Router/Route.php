<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\Router;

use Swift\DependencyInjection\Attributes\DI;
use Psr\Http\Message\RequestInterface;
use Swift\HttpFoundation\RequestMatcher;
use Swift\Router\MatchTypes\MatchTypeInterface;
use Swift\Router\RouteInterface;
use Swift\Router\RouteParameter;
use Swift\Router\RouteParameterBag;
use Swift\Router\RouteTagBag;
use Swift\Router\Utils;
use Swift\Security\Authorization\AuthorizationType;

/**
 * Class Route
 * @package Swift\WebSocket\Router
 */
#[DI( exclude: true )]
class Route implements RouteInterface {

    /** @var RouteTagBag $tags */
    private RouteTagBag $tags;

    /** @var RouteParameterBag $params */
    private RouteParameterBag $params;

    /** @var MatchTypeInterface[] */
    private array $matchTypes = [];
    
    /**
     * Route constructor.
     *
     * @param string|null $name
     * @param string      $regex      The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param string      $controller FQCN of the controller class
     * @param array       $authType
     * @param array       $isGranted
     * @param array       $tags
     */
    public function __construct(
        private string|null $name,
        private string $regex,
        private string $controller,
        private array $authType,
        private array $isGranted,
        array $tags = [],
    ) {
        $this->tags = new RouteTagBag($tags);
        $this->params = new RouteParameterBag($tags);
    }

    /**
     * Validate whether given route matches the passed request
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function matchesRequest( RequestInterface $request ): bool {
        $requestUrl         = $request->getUri()->getPath();
        $lastRequestUrlChar = $requestUrl[ strlen( $requestUrl ) - 1 ];

        if ( $this->getFullPath() === '*' ) {
            // * wildcard (matches all)
            return true;
        }

        if ( isset( $this->getFullPath()[0] ) && $this->getFullPath()[0] === '@' ) {
            // @ regex delimiter
            $pattern      = '`' . substr( $this->getFullPath(), 1 ) . '`u';
            $match        = preg_match( $pattern, $requestUrl, $params ) === 1;
            $this->params = Utils::formatRouteParams( $params );

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
     * Get full route path including controller base
     *
     * @return string|null
     */
    public function getFullPath(): ?string {
        return $this->regex ?? null;
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
     * @return RouteParameterBag
     */
    public function getParamsFromPath( bool $fresh = false ): RouteParameterBag {
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
     * @return RouteParameterBag
     */
    private function updateParams( array $params ): RouteParameterBag {
        foreach (Utils::formatRouteParams( $params ) as $name => $value) {
            if ($this->params->has($name)) {
                $this->params->get($name)->setValue($this->params->get($name)->getType()->parseValue($value, $this->request));
            }
        }

        return $this->params;
    }

    /**
     * @return RouteParameterBag
     */
    public function getParams(): RouteParameterBag {
        return $this->params;
    }

    /**
     * @param RouteParameter[]|RouteParameterBag $params
     */
    public function setParams( array|RouteParameterBag $params ): void {
        $this->params = is_array($params) ? new RouteParameterBag($params) : $params;
    }

    /**
     * @return \Swift\Router\Types\RouteMethod[]
     */
    public function getMethods(): array {
        return [];
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
        return null;
    }

    /**
     * @param string|null $action
     */
    public function setAction( ?string $action ): void {
    
    }

    /**
     * @return bool
     */
    public function isAuthRequired(): bool {
        return !in_array(AuthorizationType::PUBLIC_ACCESS, $this->authType, true);
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
     * @param RouteInterface|null $route
     */
    public function setControllerRoute( RouteInterface|null $route ): void {
    
    }

    /**
     * @return RouteInterface|null
     */
    public function getControllerRoute(): ?RouteInterface {
        return $this->controllerRoute ?? null;
    }

    /**
     * @return RouteTagBag
     */
    public function getTags(): RouteTagBag {
        return $this->tags;
    }
    
    
    /**
     * @inheritDoc
     */
    public function methodApplies( \Swift\Router\Types\RouteMethod $method ): bool {
        return true;
    }
}