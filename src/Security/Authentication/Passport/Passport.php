<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Passport;

use JetBrains\PhpStorm\Pure;
use Swift\Security\Authentication\Passport\Credentials\CredentialsInterface;
use Swift\Security\Authentication\Passport\Stamp\StampInterface;
use Swift\Security\User\UserInterface;

/**
 * Class Passport
 * @package Swift\Security\Authentication\Passport
 */
class Passport implements PassportInterface {

    private array $stamps = array();
    public AttributesBag $attributes;

    /**
     * Passport constructor.
     *
     * @param UserInterface $user
     * @param CredentialsInterface $credentials
     * @param array $stamps
     * @param array $attributes
     */
    public function __construct(
        private UserInterface $user,
        private CredentialsInterface $credentials,
        array $stamps = array(),
        array $attributes = array(),
    ) {
        $this->credentials->validateCredentials($this->user);
        $this->attributes = new AttributesBag($attributes);

        foreach ($stamps as $stamp) {
            $this->stamps[get_class($stamp)] = $stamp;
        }
    }

    /**
     * @inheritDoc
     */
    public function getUser(): UserInterface {
        return $this->user;
    }

    /**
     * @inheritDoc
     */
    public function getStamps(): array {
        return $this->stamps;
    }

    /**
     * @inheritDoc
     */
    public function getStamp( string $stamp ): ?StampInterface {
        return $this->stamps[$stamp] ?? null;
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function hasStamp( string $stamp ): bool {
        return array_key_exists($stamp, $this->stamps);
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): AttributesBag {
        return $this->attributes;
    }


}