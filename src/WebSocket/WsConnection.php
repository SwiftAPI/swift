<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket;


use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Swift\Security\Authentication\Passport\PassportInterface;

class WsConnection extends \Ratchet\WebSocket\WsConnection {
    
    protected RequestInterface $request;
    /** @var \Ratchet\RFC6455\Messaging\Message[] $messages */
    protected array $messages = [];
    
    protected PassportInterface $passport;
    
    public function __construct(
        ConnectionInterface $conn,
        RequestInterface    $request
    ) {
        $this->request = $request;
        
        parent::__construct( $conn );
    }
    
    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest(): RequestInterface {
        return $this->request;
    }
    
    /**
     * @return \Swift\Security\Authentication\Passport\PassportInterface|null
     */
    public function getPassport(): ?PassportInterface {
        return $this->passport ?? null;
    }
    
    /**
     * @return int|null
     */
    public function getResourceId(): ?int {
        return $this?->resourceId ?? null;
    }
    
    /**
     * @return \Ratchet\RFC6455\Messaging\Message[]
     */
    public function getMessages(): array {
        return $this->messages;
    }
    
    /**
     * @param \Ratchet\RFC6455\Messaging\Message $message
     */
    public function addMessage( \Ratchet\RFC6455\Messaging\Message $message ): void {
        $this->messages[] = $message;
    }
    
    public function __set( $name, $value ): void {
        if ( $name === 'request' ) {
            $this->request = $value;
            
            return;
        }
        if ( $name === 'passport' ) {
            $this->passport = $value;
            
            return;
        }
        
        parent::__set( $name, $value );
    }
    
    
}