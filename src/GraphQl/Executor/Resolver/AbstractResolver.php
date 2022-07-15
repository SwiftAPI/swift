<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Executor\Resolver;


use Psr\Http\Message\RequestInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\HttpFoundation\ServerRequest;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\AuthorizationCheckerInterface;
use Swift\Security\Security;
use Swift\Security\User\UserInterface;

#[Autowire]
class AbstractResolver implements ResolverInterface {
    
    protected \Swift\HttpFoundation\RequestInterface $request;
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected Security $security;
    
    /**
     * @param \Swift\HttpFoundation\RequestInterface $serverRequest
     */
    #[Autowire]
    public function setRequest( #[Autowire( serviceId: ServerRequest::class )] RequestInterface $serverRequest ): void {
        $this->request = $serverRequest;
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
    
    /**
     * Get current request
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
        return $this->request;
    }
    
    /**
     * @return UserInterface|null
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
        $this->authorizationChecker->denyUnlessGranted( $attributes, $subject, $strategy );
    }
    
}