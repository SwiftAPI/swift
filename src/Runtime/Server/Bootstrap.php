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
use Swift\Application\Bootstrap\Autoloading\Autoloader;
use Swift\Application\Bootstrap\DependencyInjection\DependencyInjection;
use Swift\Configuration\Configuration;
use Swift\Console\ErrorLogger;
use Swift\DependencyInjection\ServiceLocator;
use Swift\Kernel\Deprecations\Deprecation;
use Swift\Kernel\Deprecations\DeprecationLevel;

class Bootstrap {
    
    public static function createServer(): ServerInterface {
        try {
            // set up autoloading
            $autoloaderBootstrap = new Autoloader();
            $autoloaderBootstrap->initialize();
            
            // set up DI
            $diBootstrap = new DependencyInjection();
            $diBootstrap->initialize();
        } catch ( Exception $e ) {
            echo 'Autoload error: ' . $e->getMessage();
            exit( 0 );
        }
        
        try {
            $serviceLocator = new ServiceLocator();
            
            // Set timezone
            /** @var Configuration|null $configuration */
            $configuration = $serviceLocator->get( Configuration::class );
            
            if ( ! $configuration?->get( 'runtime.enabled', 'runtime' ) ) {
                echo 'Runtime is not enabled';
                exit( 0 );
            }
            
            define( 'SWIFT_RUNTIME', true);
            
            date_default_timezone_set( $configuration?->get( 'app.timezone', 'app' ) ?? 'Europe/Amsterdam' );
            
            
            // Set Debugging
            Deprecation::setDeprecationLevel(
                $configuration?->get( 'app.debug', 'app' ) ?
                    DeprecationLevel::TRIGGER_ERROR : DeprecationLevel::NONE
            );
            
            // Build to application
            return $serviceLocator->get( Server::class );
        } catch ( \Throwable $e ) {
            (new ErrorLogger())->print( $e );
            exit( 0 );
        }
    }
    
}