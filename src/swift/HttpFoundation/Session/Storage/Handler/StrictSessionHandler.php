<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Storage\Handler;

/**
 * Adds basic `SessionUpdateTimestampHandlerInterface` behaviors to another `SessionHandlerInterface`.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class StrictSessionHandler extends AbstractSessionHandler {

    private \SessionHandlerInterface $handler;
    private $doDestroy;

    public function __construct( \SessionHandlerInterface $handler ) {
        if ( $handler instanceof \SessionUpdateTimestampHandlerInterface ) {
            throw new \LogicException( sprintf( '"%s" is already an instance of "SessionUpdateTimestampHandlerInterface", you cannot wrap it with "%s".', get_debug_type( $handler ), self::class ) );
        }

        $this->handler = $handler;
    }

    /**
     * @return bool
     */
    public function open( $savePath, $sessionName ): bool {
        parent::open( $savePath, $sessionName );

        return $this->handler->open( $savePath, $sessionName );
    }

    /**
     * @return bool
     */
    public function updateTimestamp( $sessionId, $data ): bool {
        return $this->write( $sessionId, $data );
    }

    /**
     * @return bool
     */
    public function destroy( $sessionId ): bool {
        $this->doDestroy = true;
        $destroyed       = parent::destroy( $sessionId );

        return $this->doDestroy ? $this->doDestroy( $sessionId ) : $destroyed;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy( string $sessionId ): bool {
        $this->doDestroy = false;

        return $this->handler->destroy( $sessionId );
    }

    /**
     * @return bool
     */
    public function close(): bool {
        return $this->handler->close();
    }

    /**
     * @return bool
     */
    public function gc( $maxlifetime ): bool {
        return $this->handler->gc( $maxlifetime );
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead( string $sessionId ): string {
        return $this->handler->read( $sessionId );
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite( string $sessionId, string $data ): bool {
        return $this->handler->write( $sessionId, $data );
    }
}
