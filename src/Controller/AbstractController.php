<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Controller;


use Swift\HttpFoundation\RequestInterface;
use Swift\HttpFoundation\ServerRequest;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\KernelDiTags;
use Swift\Router\RouteInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\AuthorizationCheckerInterface;
use Swift\Security\Security;
use Swift\Security\User\UserInterface;

/**
 * Class AbstractController
 * @package Swift\Controller
 */
#[DI(tags: [KernelDiTags::CONTROLLER]), Autowire]
abstract class AbstractController implements ControllerInterface {

    protected RouteInterface $route;
    protected RequestInterface $request;
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected Security $security;

    /**
     * @return RouteInterface
     */
    public function getRoute(): RouteInterface {
        return $this->route;
    }

    /**
     * @param RouteInterface $route
     */
    public function setRoute( RouteInterface $route ): void {
        $this->route = $route;
    }

    /**
     * @param RequestInterface $serverRequest
     */
    #[Autowire]
    public function setRequest( #[Autowire(serviceId: ServerRequest::class)] RequestInterface $serverRequest ): void {
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
    public function denyAccessUnlessGranted(array $attributes, mixed $subject = null, string $strategy = null): void {
        $this->authorizationChecker->denyUnlessGranted($attributes, $subject, $strategy);
    }


}