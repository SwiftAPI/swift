<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router;

use Swift\Router\MatchTypes\MatchType;
use Swift\Router\MatchTypes\MatchTypeInterface;

/**
 * Class Utils
 * @package Swift\Router
 */
class Utils {
    
    /**
     * @param array $params
     *
     * @return RouteParameterBag
     */
    public static function formatRouteParams( array $params ): RouteParameterBag {
        foreach ( $params as $key => $value ) {
            if ( is_numeric( $key ) ) {
                unset( $params[ $key ] );
            }
        }
        
        return new RouteParameterBag( $params );
    }
    
    /**
     * @param string               $path
     * @param MatchTypeInterface[] $matchTypes
     *
     * @return RouteParameterBag
     */
    public static function getRouteParametersFromPath( string $path, array &$matchTypes = [] ): RouteParameterBag {
        $parameters = new RouteParameterBag();
        if ( preg_match_all( '`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $path, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as [ $block, $pre, $type, $param, $optional ] ) {
                if ( ! array_key_exists( $type, $matchTypes ) ) {
                    $matchTypes[ $type ] = new MatchType( $type, $type );
                }
                $matchType = $matchTypes[ $type ];
                $parameters->set( $param, new RouteParameter( $block, $pre, $matchType, $param, $optional ) );
            }
        }
        
        return $parameters;
    }
    
    /**
     * @param string               $route
     * @param MatchTypeInterface[] $matchTypes
     *
     * @return string
     */
    public static function routePathToRegex( string $route, array $matchTypes = [] ): string {
        if ( preg_match_all( '`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as [ $block, $pre, $type, $param, $optional ] ) {
                
                if ( isset( $matchTypes[ $type ] ) ) {
                    $type = $matchTypes[ $type ]->getRegex();
                }
                if ( $pre === '.' ) {
                    $pre = '\.';
                }
                
                $optional = $optional !== '' ? '?' : null;
                
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
                
                $route = str_replace( $block, $pattern, $route );
            }
        }
        
        return "`^$route$`u";
    }
    
}