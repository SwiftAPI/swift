<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console;


use Exception;
use Swift\Application\Bootstrap\Autoloading\Autoloader;
use Swift\Application\Bootstrap\DependencyInjection\DependencyInjection;
use Swift\Configuration\Configuration;
use Swift\DependencyInjection\ServiceLocator;
use Swift\Kernel\Deprecations\Deprecation;
use Swift\Kernel\Deprecations\DeprecationLevel;

class Bootstrap {
    
    public static function createKernel(): KernelInterface {
        try {
            // set up autoloading
            $autoloaderBootstrap = new Autoloader();
            $autoloaderBootstrap->initialize();
        
            // set up DI
            $diBootstrap = new DependencyInjection();
            $diBootstrap->initialize();
        } catch (Exception $e) {
            echo 'Autoload error: ' . $e->getMessage();
            exit(1);
        }
    
        try {
            $serviceLocator = new ServiceLocator();
        
            // Set timezone
            /** @var Configuration|null $configuration */
            $configuration = $serviceLocator->get(Configuration::class);
            date_default_timezone_set( $configuration?->get('app.timezone', 'root') ?? 'Europe/Amsterdam' );
    
    
            // Set Debugging
            Deprecation::setDeprecationLevel(
                $configuration?->get( 'app.debug', 'app' ) ?
                    DeprecationLevel::TRIGGER_ERROR : DeprecationLevel::NONE
            );
        
            // Build to application
            return $serviceLocator->get( Kernel::class );
        } catch (Exception $e) {
            (new ErrorLogger())->print( $e );
            exit(0);
        }
    }
    
}