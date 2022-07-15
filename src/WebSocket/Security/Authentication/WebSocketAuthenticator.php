<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\Security\Authentication;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\Response;
use Swift\Security\Authentication\Entity\AccessTokenEntity;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Exception\InvalidCredentialsException;
use Swift\Security\Authentication\Passport\Credentials\AccessTokenCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Passport\Stamp\PreAuthenticatedStamp;
use Swift\Security\Authentication\Token\PreAuthenticatedToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\ClientUser;
use Swift\WebSocket\DiTags;
use Swift\WebSocket\HttpFoundation\JsonMessage;
use Swift\WebSocket\HttpFoundation\MessageInterface;

#[Autowire]
#[DI( tags: [ DiTags::SECURITY_AUTHENTICATOR ] )]
class WebSocketAuthenticator implements \Swift\Security\Authentication\Authenticator\AuthenticatorInterface {
    
    
    /**
     * @param \Swift\Orm\EntityManager                   $entityManager
     * @param \Swift\Security\User\UserProviderInterface $userProvider
     */
    public function __construct(
        private readonly \Swift\Orm\EntityManager                   $entityManager,
        private readonly \Swift\Security\User\UserProviderInterface $userProvider,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function supports( \Psr\Http\Message\RequestInterface $request ): bool {
        if ( ! defined( 'SWIFT_RUNTIME' ) || ! SWIFT_RUNTIME ) {
            return false;
        }
        
        return (bool) ( $request->getHeader( 'authorization' )[ 0 ] ?? null );
    }
    
    /**
     * @inheritDoc
     */
    public function authenticate( \Psr\Http\Message\RequestInterface $request ): PassportInterface {
        $accessToken = str_replace( 'Bearer ', '', $request->getHeader( 'authorization' )[ 0 ] );
        
        if ( ! $token = $this->entityManager->findOne( AccessTokenEntity::class, [ 'accessToken' => $accessToken ] ) ) {
            throw new InvalidCredentialsException( 'No valid token found', Response::HTTP_UNAUTHORIZED );
        }
        
        if ( ! $token->getUser() && ! $token->getClient() ) {
            throw new AuthenticationException( 'No user or client related to token' );
        }
        
        if ( $token->getUser() ) {
            $user = $this->userProvider->getUserById( $token->getUser()->getId() );
        } else {
            $user = new ClientUser( ...$token->getClient()->toArray() );
        }
        
        return new Passport( $user, new AccessTokenCredentials( $token ), [ new PreAuthenticatedStamp( $token ) ] );
    }
    
    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new PreAuthenticatedToken(
            user:  $passport->getUser(),
            token: $passport->getStamp( PreAuthenticatedStamp::class )->getToken(),
        );
    }
    
    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess( \Psr\Http\Message\RequestInterface $request, TokenInterface $token ): ?MessageInterface {
        return null;
    }
    
    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( \Psr\Http\Message\RequestInterface $request, AuthenticationException $authenticationException ): ?MessageInterface {
        $response          = new \stdClass();
        $response->message = $authenticationException->getMessage();
        $response->code    = $authenticationException->getCode();
    
        return new JsonMessage( $response, false );
    }
    
}