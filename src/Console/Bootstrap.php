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
use Swift\Configuration\Configuration;
use Swift\DependencyInjection\ContainerFactory;
use Swift\Kernel\Autoloader;
use Swift\Kernel\Deprecations\Deprecation;
use Swift\Kernel\Deprecations\DeprecationLevel;

class Bootstrap {
    
    public static function createKernel(): KernelInterface {
        try {
            Autoloader::initialize();
        
            $container = ContainerFactory::createContainer();
        } catch (Exception $e) {
            echo 'Autoload error: ' . $e->getMessage();
            exit(1);
        }
    
        try {
            // Set timezone
            /** @var Configuration|null $configuration */
            $configuration = $container->get(Configuration::class);
            date_default_timezone_set( $configuration?->get('app.timezone', 'root') ?? 'Europe/Amsterdam' );
    
    
            // Set Debugging
            Deprecation::setDeprecationLevel(
                $configuration?->get( 'app.debug', 'app' ) ?
                    DeprecationLevel::TRIGGER_ERROR : DeprecationLevel::NONE
            );
        
            // Build to application
            return $container->get( Kernel::class );
        } catch (Exception $e) {
            (new ErrorLogger())->print( $e );
            exit(0);
        }
    }
    
}