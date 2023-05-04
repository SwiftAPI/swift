<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Attributes;

use Attribute;
use JetBrains\PhpStorm\Pure;
use Swift\Router\Types\RouteMethod;
use Swift\Security\Authorization\AuthorizationType;

/**
 * Class Route
 * @package Swift\Annotations\Annotation
 */
#[Attribute( Attribute::TARGET_CLASS | Attribute::TARGET_METHOD )]
#[\AllowDynamicProperties]
class Route {
    
    public const GET     = RouteMethod::GET;
    public const POST    = RouteMethod::POST;
    public const PUT     = RouteMethod::PUT;
    public const PATCH   = RouteMethod::PATCH;
    public const DELETE  = RouteMethod::DELETE;
    public const OPTIONS = RouteMethod::OPTIONS;
    public const HEAD    = RouteMethod::HEAD;
    public const CONNECT = RouteMethod::CONNECT;
    public const TRACE   = RouteMethod::TRACE;
    
    
    public const TAG_ENTRYPOINT = 'ENTRYPOINT';
    
    /**
     * Route constructor.
     *
     * @param \Swift\Router\Types\RouteMethod|\Swift\Router\Types\RouteMethod[] $method
     * @param string                                                            $route     The route for this method with a leading and closing slash
     * @param string                                                            $name      Make the route easy to find back in the router and allow for reversed routing
     * @param array|string|null                                                 $authType
     * @param array|string|null                                                 $isGranted Validate user is granted certain rights or status. More on this in de Security documentation
     * @param array                                                             $tags      Provide a route with certain tags. E.g. the Security component uses this to define a route as authentication endpoint
     */
    #[Pure]
    public function __construct(
        public RouteMethod|array $method,
        public string            $route,
        public string            $name,
        public array|string|null $authType = null,
        public array|string|null $isGranted = null,
        public array             $tags = [],
    ) {
        $this->method = is_array( $this->method ) ? $this->method : [ $this->method ];
        if ( $this->authType !== null ) {
            $this->authType = is_array( $this->authType ) ? $this->authType : explode( '|', $this->authType );
            
            foreach ( $this->authType as $authLevelItem ) {
                $routeAuthLevel = $authLevelItem;
            }
        }
        
        $this->authType ??= [ AuthorizationType::PUBLIC_ACCESS ];
    }
}