<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\Server;


class IoServer extends \Ratchet\Server\IoServer {

    public function run(): void {
        // Do not start loop here, this is a kernel responsibility
    }
    
}