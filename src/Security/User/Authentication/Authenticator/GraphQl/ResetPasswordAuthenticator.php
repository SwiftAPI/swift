<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Authentication\Authenticator\GraphQl;


use Swift\DependencyInjection\Attributes\Autowire;
use Swift\HttpFoundation\RequestInterface;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\Response;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Orm\EntityManager;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Entity\AccessTokenEntity;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Exception\InvalidCredentialsException;
use Swift\Security\Authentication\Passport\Credentials\AccessTokenCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Passport\Stamp\PreAuthenticatedStamp;
use Swift\Security\Authentication\Token\AuthenticatedToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\AuthorizationRole;
use Swift\Security\User\UserProviderInterface;

/**
 * Class ResetPasswordAuthenticator
 * @package Swift\Security\Authentication\Authenticator\GraphQl
 */
#[Autowire]
final class ResetPasswordAuthenticator implements AuthenticatorInterface {

    public const TAG_RESET_PASSWORD = 'TAG_RESET_PASSWORD';
    
    /**
     * ResetPasswordAuthenticator constructor.
     *
     * @param \Swift\Orm\EntityManager $entityManager
     * @param UserProviderInterface    $userProvider
     */
    public function __construct(
        private readonly EntityManager         $entityManager,
        private readonly UserProviderInterface $userProvider,
    ) {
    }

    /**
     * Support if Route is tagged with TAG_RESET_PASSWORD
     *
     * @inheritDoc
     */
    public function supports( \Psr\Http\Message\RequestInterface $request ): bool {
        if ( $request->getUri()->getPath() !== '/graphql' ) {
            return false;
        }

        return $request->getContent()->has( 'ResetPassword' );
    }

    /**
     * @inheritDoc
     */
    public function authenticate( \Psr\Http\Message\RequestInterface $request ): PassportInterface {
        if ( empty( $request->getContent()->get( 'ResetPassword' )['resetPasswordToken'] ) || empty( $request->getContent()->get( 'ResetPassword' )['newPassword'] ) ) {
            throw new InvalidCredentialsException( 'Invalid credentials provided' );
        }

        if ( ! $token = $this->entityManager->findOne(
            AccessTokenEntity::class,
            [
                'accessToken' => $request->getContent()->get( 'ResetPassword' )['resetPasswordToken'],
                'scope'       => TokenInterface::SCOPE_RESET_PASSWORD,
            ]
        ) ) {
            throw new InvalidCredentialsException( 'No valid token found', Response::HTTP_UNAUTHORIZED );
        }

        if ( ! $token->userId ) {
            throw new AuthenticationException( 'No user related to token' );
        }

        if ( $token->userId ) {
            $user = $this->userProvider->getUserById( $token->userId );
        }

        $user?->getRoles()->set( AuthorizationRole::ROLE_CHANGE_PASSWORD );

        return new Passport( $user, new AccessTokenCredentials( $token ), array( new PreAuthenticatedStamp( $token ) ) );
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new AuthenticatedToken(
            user: $passport->getUser(),
            scope: TokenInterface::SCOPE_RESET_PASSWORD,
            token: null,
            isAuthenticated: false,
        );
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess( \Psr\Http\Message\RequestInterface $request, TokenInterface $token ): ?ResponseInterface {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( \Psr\Http\Message\RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        $response          = new \stdClass();
        $response->message = $authenticationException->getMessage();
        $response->code    = $authenticationException->getCode();

        return new JsonResponse( $response, $authenticationException->getCode() );
    }
}