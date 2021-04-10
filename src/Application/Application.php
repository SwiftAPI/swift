<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application;

if (!defined('INCLUDE_DIR')) {
    define('INCLUDE_DIR', dirname(__DIR__, 3));
}

require_once 'Bootstrap/Bootstrap.php';

use Exception;
use Swift\Application\Bootstrap\Bootstrap;
use Swift\Kernel\Kernel;

/**
 * Class Application
 * @package Swift\Application
 */
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
        /** @var Kernel $app */
        $app = $bootstrap->getContainer()->get( Kernel::class );
        $app->run();
	}

}