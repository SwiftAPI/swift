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
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\AuthorizationCheckerInterface;
use Swift\Security\Security;
use Swift\Security\User\UserInterface;

#[Autowire]
abstract class AbstractWebSocketController implements WebSocketControllerInterface {
    
    protected \Swift\HttpFoundation\RequestInterface|\Psr\Http\Message\RequestInterface $request;
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected Security $security;
    
    /**
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function setRequest( RequestInterface $request ): void {
        $this->request = $request;
    }
    
    public function getRequest(): RequestInterface {
        return $this->request;
    }
    
    /**
     * @inheritDoc
     */
    public function getCurrentUser(): ?UserInterface {
        return $this->security->getUser();
    }
    
    public function getSecurityToken(): ?TokenInterface {
        return $this->security->getToken();
    }
    
    /**
     * @inheritDoc
     */
    public function denyAccessUnlessGranted( array $attributes, mixed $subject = null, string $strategy = null ): void {
        $this->authorizationChecker->denyUnlessGranted($attributes, $subject, $strategy);
    }
    
    #[Autowire]
    public function setAuthorizationChecker( AuthorizationCheckerInterface $authorizationChecker ): void {
        $this->authorizationChecker = $authorizationChecker;
    }
    
    /**
     * @param Security $security
     */
    #[Autowire]
    public function setSecurity( Security $security ): void {
        $this->security = $security;
    }
    
    
}