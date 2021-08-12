<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console;

if (!defined('INCLUDE_DIR')) {
    define('INCLUDE_DIR', dirname(__DIR__, 5));
}

require_once INCLUDE_DIR . '/vendor/autoload.php';

use Exception;
use Swift\Application\Bootstrap\Autoloading\Autoloader;
use Swift\Application\Bootstrap\DependencyInjection\DependencyInjection;
use Swift\Configuration\Configuration;
use Swift\Kernel\ServiceLocator;

/**
 * Class CLIApplication
 * @package Swift\Console
 */
final class CLIApplication {

    /**
     * Bootstrap CLI Application
     */
    public function run(): void {
        if (PHP_SAPI !== 'cli') {
            echo 'bin/console must be run as a CLI application';
            exit(1);
        }

        try {
            // set up autoloading
            $autoloaderBootstrap = new Autoloader();
            $autoloaderBootstrap->initialize();
            
            // set up DI
            $DIBootstrap = new DependencyInjection();
            $DIBootstrap->initialize();
        } catch (Exception $e) {
            echo 'Autoload error: ' . $e->getMessage();
            exit(1);
        }

        try {
            // Build to application
            $serviceLocator = new ServiceLocator();
    
            // Set timezone
            /** @var Configuration|null $configuration */
            $configuration = $serviceLocator->get(Configuration::class);
            date_default_timezone_set( $configuration?->get('app.timezone', 'root') ?? 'Europe/Amsterdam' );
            
            $app = $serviceLocator->get( Application::class );
            $app->run();
        } catch (Exception $e) {
            while($e) {
                echo $e->getMessage();
                echo $e->getTraceAsString();
                echo "\n\n";
                $e->getPrevious();
            }
            exit(0);
        }
    }

}