<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Controller;


use Psr\Http\Message\RequestInterface;
use Swift\HttpFoundation\Exception\AccessDeniedException;
use Swift\Kernel\Attributes\DI;
use Swift\Router\RouteInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\UserInterface;

/**
 * Interface ControllerInterface
 * @package Swift\Controller
 */
interface ControllerInterface {

    public function getRoute(): RouteInterface;

    public function setRoute(RouteInterface $route): void;

    public function getRequest(): RequestInterface;

    /**
     * @return UserInterface|null
     */
    public function getCurrentUser(): ?UserInterface;

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