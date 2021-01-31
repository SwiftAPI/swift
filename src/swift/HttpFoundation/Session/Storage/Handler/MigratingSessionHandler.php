<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Storage\Handler;

use Swift\Kernel\Attributes\DI;

/**
 * Migrating session handler for migrating from one handler to another. It reads
 * from the current handler and writes both the current and new ones.
 *
 * It ignores errors from the new handler.
 *
 * @author Ross Motley <ross.motley@amara.com>
 * @author Oliver Radwell <oliver.radwell@amara.com>
 */
#[DI( autowire: false )]
class MigratingSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface {

    private \SessionHandlerInterface|\SessionUpdateTimestampHandlerInterface|StrictSessionHandler $currentHandler;
    private \SessionHandlerInterface|\SessionUpdateTimestampHandlerInterface|StrictSessionHandler $writeOnlyHandler;

    public function __construct( \SessionHandlerInterface $currentHandler, \SessionHandlerInterface $writeOnlyHandler ) {
        if ( ! $currentHandler instanceof \SessionUpdateTimestampHandlerInterface ) {
            $currentHandler = new StrictSessionHandler( $currentHandler );
        }
        if ( ! $writeOnlyHandler instanceof \SessionUpdateTimestampHandlerInterface ) {
            $writeOnlyHandler = new StrictSessionHandler( $writeOnlyHandler );
        }

        $this->currentHandler   = $currentHandler;
        $this->writeOnlyHandler = $writeOnlyHandler;
    }

    /**
     * @return bool
     */
    public function close(): bool {
        $result = $this->currentHandler->close();
        $this->writeOnlyHandler->close();

        return $result;
    }

    /**
     * @return bool
     */
    public function destroy( $sessionId ): bool {
        $result = $this->currentHandler->destroy( $sessionId );
        $this->writeOnlyHandler->destroy( $sessionId );

        return $result;
    }

    /**
     * @return bool
     */
    public function gc( $maxlifetime ): bool {
        $result = $this->currentHandler->gc( $maxlifetime );
        $this->writeOnlyHandler->gc( $maxlifetime );

        return $result;
    }

    /**
     * @return bool
     */
    public function open( $savePath, $sessionName ): bool {
        $result = $this->currentHandler->open( $savePath, $sessionName );
        $this->writeOnlyHandler->open( $savePath, $sessionName );

        return $result;
    }

    /**
     * @return string
     */
    public function read( $sessionId ): string {
        // No reading from new handler until switch-over
        return $this->currentHandler->read( $sessionId );
    }

    /**
     * @return bool
     */
    public function write( $sessionId, $sessionData ): bool {
        $result = $this->currentHandler->write( $sessionId, $sessionData );
        $this->writeOnlyHandler->write( $sessionId, $sessionData );

        return $result;
    }

    /**
     * @return bool
     */
    public function validateId( $sessionId ): bool {
        // No reading from new handler until switch-over
        return $this->currentHandler->validateId( $sessionId );
    }

    /**
     * @return bool
     */
    public function updateTimestamp( $sessionId, $sessionData ): bool {
        $result = $this->currentHandler->updateTimestamp( $sessionId, $sessionData );
        $this->writeOnlyHandler->updateTimestamp( $sessionId, $sessionData );

        return $result;
    }
}
