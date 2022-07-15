<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Authentication\Authenticator\Rest\OAuth;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\RequestInterface;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Orm\EntityManager;
use Swift\Security\Authentication\Authenticator\AuthenticatorEntrypointInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\User\Entity\OauthClientsEntity;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\Credentials\ClientCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\OathClientCredentialsToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authentication\Token\TokenStoragePoolInterface;
use Swift\Security\User\ClientUser;

/**
 * Class OAuthClientCredentialsAuthenticator
 * @package Swift\Security\Authentication\Authenticator\OAuth\Rest
 */
#[Autowire]
final class OAuthClientCredentialsAuthenticator implements AuthenticatorInterface, AuthenticatorEntrypointInterface {
    
    /**
     * OAuthClientCredentialsAuthenticator constructor.
     *
     * @param \Swift\Orm\EntityManager  $entityManager
     * @param TokenStoragePoolInterface $tokenStoragePool
     */
    public function __construct(
        private readonly EntityManager             $entityManager,
        private readonly TokenStoragePoolInterface $tokenStoragePool,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function supports( \Psr\Http\Message\RequestInterface $request ): bool {
        $requestContent = $request->getContent();
        
        return ! empty( $requestContent->get( 'grant_type' ) ) &&
               ( $requestContent->get( 'grant_type' ) === 'client_credentials' ) &&
               ! empty( $requestContent->get( 'client_id' ) ) &&
               ! empty( $requestContent->get( 'client_secret' ) );
    }
    
    /**
     * @inheritDoc
     */
    public function authenticate( \Psr\Http\Message\RequestInterface $request ): PassportInterface {
        $requestContent = $request->getContent();
        $client         = $this->entityManager->findOne(
            OauthClientsEntity::class,
            [
                'clientId' => $requestContent->get( 'client_id' ),
            ]
        );
        
        if ( ! $client ) {
            throw new AuthenticationException( 'Client not found' );
        }
        
        $clientUser = ClientUser::fromClientEntity( $client, $this->entityManager );
        
        return new Passport( $clientUser, new ClientCredentials( $clientUser->getCredential()->getCredential() ) );
    }
    
    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new OathClientCredentialsToken( $passport->getUser() );
    }
    
    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess( \Psr\Http\Message\RequestInterface $request, /** @var OathClientCredentialsToken */ TokenInterface $token ): ?ResponseInterface {
        // Store refresh token
        $this->tokenStoragePool->setToken( $token->getRefreshToken() );
        
        return null;
    }
    
    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( \Psr\Http\Message\RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        return new JsonResponse( JsonResponse::$reasonPhrases[ JsonResponse::HTTP_UNAUTHORIZED ], JsonResponse::HTTP_UNAUTHORIZED );
    }
    
    
}