<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication;

use Psr\Http\Message\RequestInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Kernel;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\Credentials\NullCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\TokenStorageInterface;
use Swift\Security\Security;
use Swift\Security\User\NullUser;
use Swift\Security\User\UserInterface;

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
     * @param TokenStorageInterface $tokenStorage
     * @param Security $security
     */
    public function __construct(
        private Kernel $kernel,
        private TokenStorageInterface $tokenStorage,
        private Security $security,
    ) {
    }

    /**
     * @param RequestInterface $request
     *
     * @return PassportInterface
     */
    public function authenticate( RequestInterface $request ): PassportInterface {
        if ($authenticator = $this->getAuthenticator($request)) {
            try {
                $passport = $authenticator->authenticate($request);
                $token    = $authenticator->createAuthenticatedToken($passport);
                $this->tokenStorage->setToken($token);
                if ($response = $authenticator->onAuthenticationSuccess($request, $token)) {
                    $this->kernel->finalize($response);
                }

                $this->security->setPassport($passport);
                $this->security->setUser($token->getUser());
                $this->security->setToken($token);

                return $passport;
            } catch (AuthenticationException $authenticationException) {
                if ($response = $authenticator->onAuthenticationFailure($request, $authenticationException)) {
                    $this->kernel->finalize($response);
                }
            }
        }

        $token = new Token\Token(new NullUser(), null, false);
        $passport = new Passport($token->getUser(), new NullCredentials());
        $this->security->setPassport($passport);
        $this->security->setUser($token->getUser());
        $this->security->setToken($token);

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
        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->supports($request)) {
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
    public function setAuthenticators( #[Autowire(tag: DiTags::SECURITY_AUTHENTICATOR)] iterable $authenticators ): void {
        foreach ($authenticators as /** @var AuthenticatorInterface */ $authenticator) {
            $this->authenticators[$authenticator::class] = $authenticator;
        }
    }

}