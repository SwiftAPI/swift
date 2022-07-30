<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Kernel\DependencyInjection\CompilerPass;


use Swift\DependencyInjection\Container;

class RequestMiddlewareCompilerPass implements \Swift\DependencyInjection\CompilerPass\CompilerPassInterface {
    
    public function process( Container $container ): void {
        foreach ( $container->getDefinitions() as $definition ) {
            $reflection = $container->getReflectionClass( $definition->getClass() );
            if ( $reflection?->implementsInterface( \Swift\Kernel\Middleware\MiddlewareInterface::class ) ||
                 $reflection?->implementsInterface( \Psr\Http\Server\MiddlewareInterface::class )
            ) {
                $definition->addTag( 'kernel.request.middleware' );
            }
            
        }
    }
    
}