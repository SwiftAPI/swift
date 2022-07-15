<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\Controller;

use Psr\Http\Message\RequestInterface;
use Ratchet\RFC6455\Messaging\Message;
use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\Exception\AccessDeniedException;
use Swift\Runtime\RuntimeDiTags;
use Swift\WebSocket\Exceptions\WebsocketErrorException;
use Swift\WebSocket\HttpFoundation\MessageInterface;
use Swift\WebSocket\WsConnection;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\UserInterface;

#[DI( tags: [ RuntimeDiTags::SOCKET_CONTROLLER ] )]
interface WebSocketControllerInterface {
    
    /**
     * @param \Swift\WebSocket\WsConnection $connection
     *
     * @return \Swift\WebSocket\HttpFoundation\MessageInterface|null
     */
    public function onOpen( WsConnection $connection ): ?MessageInterface;
    
    /**
     * @param \Swift\WebSocket\WsConnection $connection
     *
     * @return \Swift\WebSocket\HttpFoundation\MessageInterface|null
     */
    public function onClose( WsConnection $connection ): ?MessageInterface;
    
    /**
     * @param \Swift\WebSocket\WsConnection $connection
     * @param \Ratchet\RFC6455\Messaging\Message    $message
     * @param \SplObjectStorage                     $clients
     *
     * @return \Swift\WebSocket\HttpFoundation\MessageInterface|null
     */
    public function onMessage( WsConnection $connection, Message $message, \SplObjectStorage $clients ): ?MessageInterface;
    
    /**
     * @param \Swift\WebSocket\WsConnection                       $connection
     * @param \Swift\WebSocket\Exceptions\WebsocketErrorException $exception
     *
     * @return \Swift\WebSocket\HttpFoundation\MessageInterface|null
     */
    public function onError( WsConnection $connection, WebsocketErrorException $exception ): ?MessageInterface;
    
    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest(): RequestInterface;
    
    /**
     * @return UserInterface|null
     */
    public function getCurrentUser(): ?UserInterface;
    
    /**
     * @return \Swift\Security\Authentication\Token\TokenInterface|null
     */
    public function getSecurityToken(): ?TokenInterface;
    
    /**
     * Throw exception when access denied
     *
     * @param array $attributes
     * @param mixed|null $subject
     * @param string|null $strategy
     *
     * @return void
     *
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessGranted( array $attributes, mixed $subject = null, string $strategy = null ): void;
    
}