<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport;

/**
 * Interface UserInterface
 * @package Swift\Security\Authentication\Passport
 */
interface UserInterface {

    /**
     * Get credentials belonging to user
     *
     * @return string
     */
    public function getCredential(): string;

}