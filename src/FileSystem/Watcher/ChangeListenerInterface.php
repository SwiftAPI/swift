<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher;


use Swift\FileSystem\Watcher\Config\WatchList;

interface ChangeListenerInterface {
    
    public function start(WatchList $watchList): void;
    
    public function onChange(callable $callback): void;
    
}