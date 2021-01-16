<?php declare(strict_types=1);

namespace Swift\Application;

if (!defined('INCLUDE_DIR')) {
    define('INCLUDE_DIR', dirname(__DIR__, 3));
}

require_once 'Bootstrap/Bootstrap.php';

use Exception;
use Swift\Application\Bootstrap\Bootstrap;
use Swift\Kernel\Application as Kernel;


class Application {

	/**
	 * Method to run application
	 *
	 * @throws Exception
	 */
	public function run() : void {
        $bootstrap = new Bootstrap();
        $bootstrap->initialize();

        // Build to application
        global $containerBuilder;
        /** @var Kernel $app */
        $app = $containerBuilder->get( Kernel::class );
        $app->run();
	}


}