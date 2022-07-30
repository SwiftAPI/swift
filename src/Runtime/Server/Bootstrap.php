<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Runtime\Server;


use Exception;
use Swift\Configuration\Configuration;
use Swift\Console\ErrorLogger;
use Swift\DependencyInjection\ContainerFactory;
use Swift\Kernel\Autoloader;
use Swift\Kernel\Deprecations\Deprecation;
use Swift\Kernel\Deprecations\DeprecationLevel;

class Bootstrap {
    
    public static function createServer(): ServerInterface {
        try {
            Autoloader::initialize();
            
            $container = ContainerFactory::createContainer();
        } catch ( Exception $e ) {
            echo 'Autoload error: ' . $e->getMessage();
            exit( 0 );
        }
        
        try {
            // Set timezone
            /** @var Configuration|null $configuration */
            $configuration = $container->get( Configuration::class );
            
            if ( ! $configuration?->get( 'runtime.enabled', 'runtime' ) ) {
                echo 'Runtime is not enabled';
                exit( 0 );
            }
            
            define( 'SWIFT_RUNTIME', true);
            
            date_default_timezone_set( $configuration?->get( 'app.timezone', 'app' ) ?? 'Europe/Amsterdam' );
            
            Deprecation::setDeprecationLevel(
                $configuration?->get( 'app.debug', 'app' ) ?
                    DeprecationLevel::TRIGGER_ERROR : DeprecationLevel::NONE
            );
            
            return $container->get( Server::class );
        } catch ( \Throwable $e ) {
            (new ErrorLogger())->print( $e );
            exit( 0 );
        }
    }
    
}