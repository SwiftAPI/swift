<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\RequestInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\RequestMatcher;
use Swift\Router\MatchTypes\MatchTypeInterface;
use Swift\Router\Types\RouteMethod;
use Swift\Security\Authorization\AuthorizationType;


#[DI( exclude: true )]
class ControllerRoute implements RouteInterface {
    
    /** @var RequestInterface $request */
    private RequestInterface $request;
    
    /** @var RouteTagBag $tags */
    private RouteTagBag $tags;
    
    /** @var RouteParameterBag $params */
    private RouteParameterBag $params;
    
    /** @var MatchTypeInterface[] */
    private array $matchTypes = [];
    
    /**
     * Route constructor.
     *
     * @param string|null                       $name
     * @param string                            $regex      The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param \Swift\Router\Types\RouteMethod[] $methods    HTTP methods on which the route applies. Methods must be one or more of 5 HTTP Methods (GET|POST|PATCH|PUT|DELETE)
     * @param string                            $controller FQCN of the controller class
     * @param string|null                       $action
     * @param array                             $authType
     * @param array                             $isGranted
     * @param array                             $tags
     */
    public function __construct(
        private string|null $name,
        private string      $regex,
        private array       $methods,
        private string      $controller,
        private string|null $action,
        private array       $authType,
        private array       $isGranted,
        array               $tags = [],
    ) {
        $this->tags   = new RouteTagBag( $tags );
        $this->params = new RouteParameterBag();
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
        $method = RouteMethod::from( $request->getMethod() );
        if ( ! $this->methodApplies( $method ) ) {
            return false;
        }
        
        $requestUrl         = $request->getUri()->getPath();
        $lastRequestUrlChar = $requestUrl[ strlen( $requestUrl ) - 1 ];
        
        if ( $this->getFullPath() === '*' ) {
            // * wildcard (matches all)
            return true;
        }
        
        if ( isset( $this->getFullPath()[ 0 ] ) && $this->getFullPath()[ 0 ] === '@' ) {
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
            $this->params = $this->updateParams( $matcher->getParams() );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Checks whether a given HTTPMethod applies for this route
     *
     * @param \Swift\Router\Types\RouteMethod $method
     *
     * @return bool
     */
    #[Pure]
    public function methodApplies( \Swift\Router\Types\RouteMethod $method ): bool {
        return in_array( $method, $this->methods, false );
    }
    
    /**
     * Get full route path including controller base
     *
     * @return string|null
     */
    #[Pure]
    public function getFullPath(): string|null {
        return $this->regex ?? null;
    }
    
    /**
     * Get full route regex including controller base
     *
     * @return string|null
     */
    public function getFullRegex(): string|null {
        $route = $this->getFullPath();
        
        foreach ( $this->getParamsFromPath( true ) as $parameter ) {
            $type = $parameter->type;
            
            if ( $parameter->type instanceof MatchTypeInterface ) {
                $type = $type->getRegex();
            }
            
            
            $pre = $parameter->pre;
            if ( $parameter->pre === '.' ) {
                $pre = '\.';
            }
            
            $optional = $parameter->optional !== '' ? '?' : null;
            $param    = $parameter->param;
            
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
            
            $route = str_replace( $parameter->block, $pattern, $route );
        }
        
        return "`^$route$`u";
    }
    
    /**
     * @param bool $fresh
     *
     * @return RouteParameterBag
     */
    public function getParamsFromPath( bool $fresh = false ): RouteParameterBag {
        if ( $fresh || ! isset( $this->params ) ) {
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
        foreach ( Utils::formatRouteParams( $params ) as $name => $value ) {
            if ( $this->params->has( $name ) ) {
                $this->params->get( $name )->setValue( $this->params->get( $name )->getType()->parseValue( $value, $this->request ) );
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
        $this->params = is_array( $params ) ? new RouteParameterBag( $params ) : $params;
    }
    
    /**
     * @return \Swift\Router\Types\RouteMethod[]
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
    public function setName( string|null $name ): void {
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
     * @return string|null
     */
    public function getControllerBase(): string|null {
        return $this->regex ?? null;
    }
    
    /**
     * @param string|null $controllerBase
     */
    public function setControllerBase( string|null $controllerBase ): void {
        $this->regex = $controllerBase ?? '';
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
    public function getAction(): string|null {
        return $this->action;
    }
    
    /**
     * @param string|null $action
     */
    public function setAction( string|null $action ): void {
        $this->action = $action;
    }
    
    /**
     * @return bool
     */
    public function isAuthRequired(): bool {
        return ! in_array( AuthorizationType::PUBLIC_ACCESS, $this->authType, true );
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
     * @return RouteTagBag
     */
    public function getTags(): RouteTagBag {
        return $this->tags;
    }
    
}