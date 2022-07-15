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
use Swift\Security\Authentication\Passport\Credentials\RefreshTokenCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\OauthAccessToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authentication\Token\TokenStorageInterface;
use Swift\Security\User\ClientUser;

/**
 * Class OAuthRefreshTokenAuthenticator
 * @package Swift\Security\Authentication\Authenticator\OAuth\Rest
 */
#[Autowire]
class OAuthRefreshTokenAuthenticator implements AuthenticatorInterface, AuthenticatorEntrypointInterface {
    
    /**
     * OAuthRefreshTokenAuthenticator constructor.
     *
     * @param \Swift\Orm\EntityManager $entityManager
     * @param TokenStorageInterface    $databaseTokenStorage
     */
    public function __construct(
        private readonly EntityManager         $entityManager,
        private readonly TokenStorageInterface $databaseTokenStorage,
    
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function supports( \Psr\Http\Message\RequestInterface $request ): bool {
        $requestContent = $request->getContent();
        
        return ! empty( $requestContent->get( 'grant_type' ) ) &&
               ( $requestContent->get( 'grant_type' ) === 'refresh_token' ) &&
               ! empty( $requestContent->get( 'refresh_token' ) );
    }
    
    /**
     * @inheritDoc
     */
    public function authenticate( \Psr\Http\Message\RequestInterface $request ): PassportInterface {
        $requestContent = $request->getContent();
        
        $token = $this->databaseTokenStorage->findOne(
            [
                'accessToken' => $requestContent->get( 'refresh_token' ),
                'scope'       => 'SCOPE_REFRESH_TOKEN',
            ]
        );
        
        if ( ! $token ) {
            throw new AuthenticationException( 'Invalid token' );
        }
        
        if ( ! $token->getClient() instanceof OauthClientsEntity ) {
            throw new AuthenticationException( 'Invalid token' );
        }
        
        $client = ClientUser::fromClientEntity( $token->getClient(), $this->entityManager );
        
        return new Passport( $client, new RefreshTokenCredentials( $token ) );
    }
    
    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new OauthAccessToken( $passport->getUser() );
    }
    
    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess( \Psr\Http\Message\RequestInterface $request, /** @var OauthAccessToken */ TokenInterface $token ): ?ResponseInterface {
        return null;
    }
    
    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( \Psr\Http\Message\RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        return new JsonResponse( JsonResponse::$reasonPhrases[ JsonResponse::HTTP_UNAUTHORIZED ], JsonResponse::HTTP_UNAUTHORIZED );
    }
    
}