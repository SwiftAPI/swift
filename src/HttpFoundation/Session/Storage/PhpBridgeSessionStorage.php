<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Storage;

use Swift\HttpFoundation\Session\Storage\Proxy\AbstractProxy;

/**
 * Allows session to be started by PHP and managed by Symfony.
 *
 * @author Drak <drak@zikula.org>
 */
class PhpBridgeSessionStorage extends NativeSessionStorage {
    /**
     * @param null $handler
     * @param MetadataBag|null $metaBag
     */
    public function __construct( $handler = null, MetadataBag $metaBag = null ) {
        if ( ! \extension_loaded( 'session' ) ) {
            throw new \LogicException( 'PHP extension "session" is required.' );
        }

        $this->setMetadataBag( $metaBag );
        $this->setSaveHandler( $handler );
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool {
        if ( $this->started ) {
            return true;
        }

        $this->loadSession();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void {
        // clear out the bags and nothing else that may be set
        // since the purpose of this driver is to share a handler
        foreach ( $this->bags as $bag ) {
            $bag->clear();
        }

        // reconnect the bags to the session
        $this->loadSession();
    }
}
