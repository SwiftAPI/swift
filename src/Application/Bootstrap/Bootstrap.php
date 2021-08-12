<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application\Bootstrap;

require_once 'Autoloading/Autoloader.php';
require_once 'DependencyInjection/DependencyInjection.php';
use Swift\Application\Bootstrap\Autoloading\Autoloader;
use Swift\Application\Bootstrap\DependencyInjection\DependencyInjection;
use Swift\Configuration\Configuration;
use Swift\Kernel\Container\Container;
use Swift\Kernel\Kernel;

/**
 * Class Bootstrap
 * @package Swift\Application\Bootstrap
 */
class Bootstrap {

    private Container $container;

    /**
     * Bootstrap Application
     */
    public function initialize(): Kernel {
        /**
         * Define the application's minimum supported PHP version as a constant so it can be referenced within the application.
         */
        define('SWIFT_MINIMUM_PHP', '8.0.0');
        
        if (version_compare(PHP_VERSION, SWIFT_MINIMUM_PHP, '<')) {
            die('Your host needs to use PHP ' . SWIFT_MINIMUM_PHP . ' or higher to run this version of Henri!');
        }

        // Saves the start time and memory usage.
        global $startTime;
        global $startMem;
        $startTime = microtime(true);
        $startMem  = memory_get_usage();

        include_once __DIR__ . '/Functions.php';


        // set up autoloading
        $autoloadBootstrap = new Autoloader();
        $autoloadBootstrap->initialize();

        // set up DI
        $DiBootstrap = new DependencyInjection();
        $this->container = $DiBootstrap->initialize();
    
        // Set timezone
        /** @var Configuration|null $configuration */
        $configuration = $this->container->get(Configuration::class);
        date_default_timezone_set( $configuration?->get('app.timezone', 'root') ?? 'Europe/Amsterdam' );

        /** @var Kernel $app */
        $app = $this->container->get(Kernel::class);

        return $app;
    }
    
}