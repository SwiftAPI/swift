<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport;

use Swift\Security\Authentication\Passport\Stamp\StampInterface;
use Swift\Security\User\UserInterface;

/**
 * Interface PassportInterface
 * @package Swift\Security\Authentication\Passport
 */
interface PassportInterface {

    /**
     * Get associated user
     *
     * @return UserInterface
     */
    public function getUser(): UserInterface;

    /**
     * Retrieve stamps
     *
     * @return StampInterface[]
     */
    public function getStamps(): array;

    /**
     * @param string $stamp
     *
     * @return StampInterface|null
     */
    public function getStamp( string $stamp ): ?StampInterface;

    /**
     * @param string $stamp
     *
     * @return bool
     */
    public function hasStamp( string $stamp ): bool;

    /**
     * Get attributes
     *
     * @return AttributesBag
     */
    public function getAttributes(): AttributesBag;

}