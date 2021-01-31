<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application\Bootstrap;

require_once 'Autoloading/Autoloader.php';
require_once 'DependencyInjection/DependencyInjection.php';
use Swift\Application\Bootstrap\Autoloading\Autoloader;
use Swift\Application\Bootstrap\DependencyInjection\DependencyInjection;
use Swift\Kernel\Container\Container;

/**
 * Class Bootstrap
 * @package Swift\Application\Bootstrap
 */
class Bootstrap {

    private Container $container;

    /**
     * Bootstrap Application
     */
    public function initialize(): void {
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

        date_default_timezone_set('Europe/Amsterdam');


        // set up autoloading
        $autoloadBootstrap = new Autoloader();
        $autoloadBootstrap->initialize();

        // set up DI
        $DiBootstrap = new DependencyInjection();
        $this->container = $DiBootstrap->initialize();
    }

    /**
     * @return Container
     */
    public function getContainer(): Container {
        return $this->container;
    }


    
}