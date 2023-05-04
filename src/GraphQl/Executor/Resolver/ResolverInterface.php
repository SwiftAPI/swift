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
use Swift\DependencyInjection\Attributes\DI;
use Swift\GraphQl\DependencyInjection\DiTags;
use Swift\HttpFoundation\Exception\AccessDeniedException;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\UserInterface;

#[DI( tags: [ DiTags::GRAPHQL_RESOLVER ] )]
interface ResolverInterface {
    
    public function getRequest(): RequestInterface;
    
    /**
     * @return UserInterface|null
     */
    public function getCurrentUser(): UserInterface|null;
    
    public function getSecurityToken(): TokenInterface|null;
    
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
    public function denyAccessUnlessGranted( array $attributes, mixed $subject = null, string|null $strategy = null ): void;
    
}