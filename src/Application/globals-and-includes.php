<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */



if (!defined('INCLUDE_DIR')) {
    define('INCLUDE_DIR', dirname(__DIR__, 5));
}
if (!defined('SWIFT_ROOT')) {
    define('SWIFT_ROOT', dirname(__DIR__, 1));
}

/**
 * Define the application's minimum supported PHP version as a constant so it can be referenced within the application.
 */
if (!defined('SWIFT_MINIMUM_PHP')) {
    define( "SWIFT_MINIMUM_PHP", '8.1.0' );
}

if (version_compare(PHP_VERSION, SWIFT_MINIMUM_PHP, '<')) {
    die('Your host needs to use PHP ' . SWIFT_MINIMUM_PHP . ' or higher to run this version of SWIFT!');
}

require_once INCLUDE_DIR . '/vendor/autoload.php';

require_once 'Bootstrap/Autoloading/Autoloader.php';
require_once 'Bootstrap/DependencyInjection/DependencyInjection.php';
require_once 'ApplicationInterface.php';
require_once 'Bootstrap/Bootstrap.php';