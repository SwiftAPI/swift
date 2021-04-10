<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport\Stamp;

use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Class PreAuthenticatedStamp
 * @package Swift\Security\Authentication\Passport\Stamp
 */
class PreAuthenticatedStamp implements StampInterface {

    /**
     * PreAuthenticatedStamp constructor.
     *
     * @param \stdClass|TokenInterface $token
     */
    public function __construct(
        private \stdClass|TokenInterface $token
    ) {
    }

    /**
     * @return \stdClass|TokenInterface
     */
    public function getToken(): TokenInterface|\stdClass {
        return $this->token;
    }



}