<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use DateTime;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Entity;
use Swift\Model\EntityInterface;
use Swift\Model\Types\FieldTypes;
use Swift\Security\Authentication\Entity\AccessTokenEntity;

/**
 * Class DatabaseTokenProvider
 * @package Swift\Security\Authentication\Token
 */
#[DI(aliases: [TokenStorageInterface::class . ' $databaseStorageInterface']), Autowire]
final class DatabaseTokenStorage implements TokenStorageInterface {

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
        $state = array(
            'accessToken' => $data->accessToken,
            'expires' => $data->expires,
        );
        if ($data->id) {
            $state['id'] = $data->id;
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