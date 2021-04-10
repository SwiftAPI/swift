<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use Swift\Kernel\Attributes\Autowire;
use Swift\Security\Authentication\DiTags;

/**
 * Class TokenStoragePool
 * @package Swift\Security\Authentication\Token
 */
#[Autowire]
class TokenStoragePool implements TokenStoragePoolInterface {

    /**
     * @var TokenStorageInterface[] $storages
     */
    private iterable $storages;

    /**
     * @inheritDoc
     */
    public function getToken( string $accessToken ): ?TokenInterface {
        foreach ($this->storages as $storage) {
            if ($token = $storage->getToken($accessToken)) {
                return $token;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function setToken( ?TokenInterface $token = null ): void {
        if (!$token) {
            return;
        }

        foreach ($this->storages as $storage) {
            if ($storage->supports($token)) {
                $storage->setToken($token);
            }
        }
    }

    #[Autowire]
    public function setTokenStorages( #[Autowire(tag: DiTags::SECURITY_TOKEN_STORAGE)] iterable $storages ): void {
        $this->storages = $storages;
    }

}