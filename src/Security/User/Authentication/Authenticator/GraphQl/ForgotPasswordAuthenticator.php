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
use Swift\HttpFoundation\ResponseInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\Credentials\EmailCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\ResetPasswordToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\UserProviderInterface;

/**
 * Class ForgotPasswordAuthenticatorGraphQl
 * @package Swift\Security\Authentication\Authenticator\GraphQl
 */
#[Autowire]
final class ForgotPasswordAuthenticator implements AuthenticatorInterface {
    
    public const TAG_FORGOT_PASSWORD = 'TAG_FORGOT_PASSWORD';
    
    /**
     * AccessTokenAuthenticator constructor.
     *
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        private readonly UserProviderInterface $userProvider,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function supports( \Psr\Http\Message\RequestInterface $request ): bool {
        if ( $request->getUri()->getPath() !== '/graphql' ) {
            return false;
        }
        
        return $request->getContent()->has( 'UserForgotPassword' ) && ! empty( $request->getContent()->get( 'UserForgotPassword' )[ 'email' ] );
    }
    
    /**
     * @inheritDoc
     */
    public function authenticate( \Psr\Http\Message\RequestInterface $request ): PassportInterface {
        if ( ! $user = $this->userProvider->getUserByEmail( $request->getContent()->get( 'UserForgotPassword' )[ 'email' ] ) ) {
            throw new AuthenticationException( 'No user found with given credentials' );
        }
        
        return new Passport( $user, new EmailCredentials( $request->getContent()->get( 'UserForgotPassword' )[ 'email' ] ) );
    }
    
    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new ResetPasswordToken(
            user:            $passport->getUser(),
            scope:           TokenInterface::SCOPE_RESET_PASSWORD,
            token:           null,
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