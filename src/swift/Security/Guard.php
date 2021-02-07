<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security;

use Psr\Http\Message\RequestInterface;
use Swift\Configuration\Configuration;
use Swift\Kernel\Attributes\Autowire;
use Swift\Security\Authentication\AuthenticationManager;
use Swift\Security\Authorization\AccessDecisionManagerInterface;

/**
 * Class Guard
 * @package Swift\Security
 */
#[Autowire]
class Guard implements GuardInterface {

    /**
     * Guard constructor.
     *
     * @param Configuration $configuration
     * @param AuthenticationManager $authenticationManager
     * @param AccessDecisionManagerInterface $accessDecisionManager
     */
    public function __construct(
        private Configuration $configuration,
        private AuthenticationManager $authenticationManager,
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    public function guard( RequestInterface $request ): void {
        $userPassport = $this->authenticationManager->authenticate($request);

        var_dump($this->accessDecisionManager);
    }


}