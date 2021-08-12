<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Model\EntityInterface;

/**
 * Class DatabaseAccessTokenStorage
 * @package Swift\Security\Authentication\Token
 */
#[DI(aliases: [TokenStorageInterface::class . ' $databaseStorageInterface']), Autowire]
final class DatabaseAccessTokenStorage implements TokenStorageInterface {

    private ?\Closure $initializer = null;
    private ?TokenInterface $token = null;

    /**
     * DatabaseTokenProvider constructor.
     *
     * @param EntityInterface $accessTokenEntity
     */
    public function __construct(
        private EntityInterface $accessTokenEntity,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function supports( TokenInterface $token ): bool {
        return true;
    }

    public function findOne( array $state ): \stdClass|null {
        return $this->accessTokenEntity->findOne($state);
    }

    /**
     * @inheritDoc
     */
    public function getToken( string $accessToken = null ): ?TokenInterface {
        if ($initializer = $this->initializer) {
            $this->initializer = null;
            $this->token = $initializer($this);
        }

        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function setToken( ?TokenInterface $token = null ): void {
        if (!$token) {
            return;
        }

        $data = $token->getData();

        $state = [
            'accessToken' => $data->accessToken,
            'expires' => $data->expires,
        ];
        if ($data->id) {
            $state['id'] = $data->id;
        } else {
            $state['clientId'] = $data->clientId ?? null;
            $state['userId'] = $data->userId ?? null;
            $state['scope'] = $data->scope ?? null;
        }

        $this->accessTokenEntity->save($state);
    }

    /**
     * Token initializer
     *
     * @param callable|null $initializer
     */
    public function setInitializer( ?callable $initializer ): void {
        $this->initializer = $initializer;
    }
}