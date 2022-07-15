<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Storage\Handler;

use ReturnTypeWillChange;
use Swift\DependencyInjection\Attributes\DI;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

/**
 * @author Ahmed TAILOULOUTE <ahmed.tailouloute@gmail.com>
 */
#[DI( autowire: false )]
class MarshallingSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface {

    private AbstractSessionHandler $handler;
    private MarshallerInterface $marshaller;

    /**
     * MarshallingSessionHandler constructor.
     *
     * @param AbstractSessionHandler $handler
     * @param MarshallerInterface $marshaller
     */
    public function __construct( AbstractSessionHandler $handler, MarshallerInterface $marshaller ) {
        $this->handler    = $handler;
        $this->marshaller = $marshaller;
    }

    /**
     * {@inheritdoc}
     */
    public function open( $savePath, $name ): bool {
        return $this->handler->open( $savePath, $name );
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool {
        return $this->handler->close();
    }

    /**
     * {@inheritdoc}
     */
    public function destroy( $sessionId ): bool {
        return $this->handler->destroy( $sessionId );
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function gc( $maxlifetime ): bool {
        return $this->handler->gc( $maxlifetime );
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function read( $sessionId ) {
        return $this->marshaller->unmarshall( $this->handler->read( $sessionId ) );
    }

    /**
     * {@inheritdoc}
     */
    public function write( $sessionId, $data ): bool {
        $failed         = [];
        $marshalledData = $this->marshaller->marshall( [ 'data' => $data ], $failed );

        if ( isset( $failed['data'] ) ) {
            return false;
        }

        return $this->handler->write( $sessionId, $marshalledData['data'] );
    }

    /**
     * {@inheritdoc}
     */
    public function validateId( $sessionId ): bool {
        return $this->handler->validateId( $sessionId );
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp( $sessionId, $data ): bool {
        return $this->handler->updateTimestamp( $sessionId, $data );
    }
}
