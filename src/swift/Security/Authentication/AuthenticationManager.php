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
use Swift\Security\Authentication\Passport\PassportInterface;

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
     */
    public function __construct(
        private Kernel $kernel,
    ) {
    }


    public function authenticate( RequestInterface $request ): PassportInterface {
        if ($authenticator = $this->getAuthenticator($request)) {
            try {
                $passport = $authenticator->authenticate($request);
                if ($response = $authenticator->onAuthenticationSuccess($request, $passport->getToken())) {
                    $this->kernel->finalize($response);
                }

                return $passport;
            } catch (AuthenticationException $authenticationException) {
                if ($response = $authenticator->onAuthenticationFailure($request, $authenticationException)) {
                    $this->kernel->finalize($response);
                }
            }
        }


    }

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