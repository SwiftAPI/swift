<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\HttpFoundation\Exception\AccessDeniedException;
use Swift\HttpFoundation\Response;
use Swift\Security\Security;

/**
 * Class AuthorizationChecker
 * @package Swift\Security\Authorization
 */
#[Autowire]
class AuthorizationChecker implements AuthorizationCheckerInterface {

    /**
     * AuthorizationChecker constructor.
     *
     * @param Security $security
     * @param AccessDecisionManagerInterface $accessDecisionManager
     */
    public function __construct(
        protected Security $security,
        protected AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isGranted( array $attributes, mixed $subject = null, string|null $strategy = null ): bool {
        return $this->accessDecisionManager->decide($this->security->getToken(), $subject, $attributes, $strategy);
    }

    /**
     * @inheritDoc
     */
    public function denyUnlessGranted( array $attributes, mixed $subject = null, string|null $strategy = null ): void {
        if (!$this->isGranted($attributes, $subject, $strategy)) {
            throw new AccessDeniedException('', Response::HTTP_UNAUTHORIZED);
        }
    }


}