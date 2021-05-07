<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Application;

if (!defined('INCLUDE_DIR')) {
    define('INCLUDE_DIR', dirname(__DIR__, 3));
}
if (!defined('SWIFT_ROOT')) {
    define('SWIFT_ROOT', dirname(__DIR__, 1));
}

require_once 'Bootstrap/Bootstrap.php';

use Exception;
use Swift\Application\Bootstrap\Bootstrap;

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
        $app = $bootstrap->initialize();

        $app->run();
	}

}