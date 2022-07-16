<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

require dirname( __DIR__, 6 ) . '/vendor/autoload.php';
require_once 'RuntimeApplication.php';

$CliApplication = new \Swift\Runtime\Server\RuntimeApplication();
$CliApplication->run();