<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication;

use Swift\HttpFoundation\RequestInterface;
use Swift\Events\EventDispatcher;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Kernel;
use Swift\Router\Attributes\Route;
use Swift\Router\RouterInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorEntrypointInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Events\AuthenticationFailedEvent;
use Swift\Security\Authentication\Events\AuthenticationFinishedEvent;
use Swift\Security\Authentication\Events\AuthenticationSuccessEvent;
use Swift\Security\Authentication\Events\AuthenticationTokenCreatedEvent;
use Swift\Security\Authentication\Events\CheckPassportEvent;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\Credentials\NullCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authentication\Token\TokenStorageInterface;
use Swift\Security\Authentication\Token\TokenStoragePoolInterface;
use Swift\Security\Security;
use Swift\Security\User\NullUser;

/**
 * Class AuthenticationManager
 * @package Swift\Security\Authentication
 */
#[Autowire]
class AuthenticationManager {

    /** @var AuthenticatorInterface[] $authenticators */
    private array $authenticators = array();

    /**
     * AuthenticationManager constructor.
     *
     * @param Kernel $kernel
     * @param TokenStoragePoolInterface $tokenStoragePool
     * @param Security $security
     * @param EventDispatcher $eventDispatcher
     * @param RouterInterface $router
     */
    public function __construct(
        private Kernel $kernel,
        private TokenStoragePoolInterface $tokenStoragePool,
        private Security $security,
        private EventDispatcher $eventDispatcher,
        private RouterInterface $router,
    ) {
    }

    /**
     * @param RequestInterface $request
     *
     * @return PassportInterface
     */
    public function authenticate( RequestInterface $request ): PassportInterface {
        if ( $authenticator = $this->getAuthenticator( $request ) ) {
            try {
                // Get the passport
                $passport = $authenticator->authenticate( $request );

                // Option for additional passport validation
                $this->eventDispatcher->dispatch( new CheckPassportEvent( $authenticator, $passport ) );

                // Create authenticated token
                $token = $authenticator->createAuthenticatedToken( $passport );
                $token = $this->eventDispatcher->dispatch( new AuthenticationTokenCreatedEvent( $token ) )->getToken();

                // Store the token
                $this->tokenStoragePool->setToken( $token );

                // Finalize request with provided response
                if ( $response = $authenticator->onAuthenticationSuccess( $request, $token ) ) {
                    $this->kernel->finalize( $response );
                }

                $this->security->setPassport( $passport );
                $this->security->setUser( $token->getUser() );
                $this->security->setToken( $token );

                $this->eventDispatcher->dispatch( new AuthenticationSuccessEvent( $token, $passport, $request, $authenticator ) );

                return $passport;
            } catch ( AuthenticationException $authenticationException ) {
                if ( $response = $authenticator->onAuthenticationFailure( $request, $authenticationException ) ) {
                    $this->eventDispatcher->dispatch( new AuthenticationFailedEvent( $request, $authenticator, $authenticationException ) );
                    $this->kernel->finalize( $response );
                }
                $this->eventDispatcher->dispatch( new AuthenticationFailedEvent( $request, $authenticator, $authenticationException ) );
            }
        }

        $token    = new Token\NullToken( new NullUser(), TokenInterface::SCOPE_ACCESS_TOKEN, null, false );
        $passport = new Passport( $token->getUser(), new NullCredentials() );
        $this->security->setPassport( $passport );
        $this->security->setUser( $token->getUser() );
        $this->security->setToken( $token );

        $this->eventDispatcher->dispatch( new AuthenticationFinishedEvent( $token, $passport, $request ) );

        return $passport;
    }

    /**
     * Return first authentication manager which claims to have support for the given request
     *
     * @param RequestInterface $request
     *
     * @return AuthenticatorInterface|null
     */
    private function getAuthenticator( RequestInterface $request ): ?AuthenticatorInterface {
        $entryPoint = $this->router->getCurrentRoute()->getTags()->has(Route::TAG_ENTRYPOINT);

        foreach ( $this->authenticators as $authenticator ) {
            if (($entryPoint && !$authenticator instanceof AuthenticatorEntrypointInterface) ||
                (!$entryPoint && $authenticator instanceof AuthenticatorEntrypointInterface)
            ) {
                continue;
            }
            if ( $authenticator->supports( $request ) ) {
                return $authenticator;
            }
        }

        return null;
    }


    /**
     * Inject authenticators
     *
     * @param iterable $authenticators
     */
    #[Autowire]
    public function setAuthenticators( #[Autowire( tag: DiTags::SECURITY_AUTHENTICATOR )] iterable $authenticators ): void {
        foreach ( $authenticators as /** @var AuthenticatorInterface */ $authenticator ) {
            $this->authenticators[ $authenticator::class ] = $authenticator;
        }
    }

}