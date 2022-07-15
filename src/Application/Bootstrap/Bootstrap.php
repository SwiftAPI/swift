<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application\Bootstrap;

use Swift\Application\Bootstrap\Autoloading\Autoloader;
use Swift\Application\Bootstrap\DependencyInjection\DependencyInjection;
use Swift\Configuration\Configuration;
use Swift\Kernel\Deprecations\Deprecation;
use Swift\Kernel\Deprecations\DeprecationLevel;
use Swift\Kernel\Kernel;
use Swift\Kernel\KernelInterface;

/**
 * Class Bootstrap
 * @package Swift\Application\Bootstrap
 */
class Bootstrap {
    
    /**
     * Bootstrap Application
     */
    public static function createKernel(): KernelInterface {
        include_once __DIR__ . '/Functions.php';


        // set up autoloading
        $autoloadBootstrap = new Autoloader();
        $autoloadBootstrap->initialize();

        // set up DI
        $diBootstrap = new DependencyInjection();
        $container   = $diBootstrap->initialize();
    
        // Set timezone
        /** @var Configuration|null $configuration */
        $configuration = $container->get( Configuration::class);
        date_default_timezone_set( $configuration?->get('app.timezone', 'root') ?? 'Europe/Amsterdam' );
        
        // Set Debugging
        Deprecation::setDeprecationLevel(
            $configuration?->get( 'app.debug', 'app' ) ?
                DeprecationLevel::TRIGGER_ERROR : DeprecationLevel::NONE
        );

        /** @var KernelInterface $app */
        $kernel = $container->get( Kernel::class);

        return $kernel;
    }
    
}