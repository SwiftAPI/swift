<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;

use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;

/**
 * Class User
 * @package Swift\Security\User
 */
#[DI(aliases: [UserInterface::class . ' $user']), Autowire]
class User implements UserInterface {

    /**
     * User constructor.
     *
     * @param UserStorageInterface $userStorage
     */
    public function __construct(
        private UserStorageInterface $userStorage,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCredential(): string {
        // TODO: Implement getCredential() method.
    }
}