<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport;

use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Interface PassportInterface
 * @package Swift\Security\Authentication\Passport
 */
interface PassportInterface {

    public function getToken(): TokenInterface;

}