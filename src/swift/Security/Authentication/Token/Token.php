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
use stdClass;
use Swift\Kernel\Attributes\DI;
use Swift\Security\User\UserInterface;


/**
 * Class Token
 * @package Swift\Security\Authentication\Token
 */
#[DI(autowire: false)]
class Token implements TokenInterface {

    protected ?int $id = null;
    protected ?string $clientId = null;
    protected ?int $userId = null;
    protected DateTime $expires;
    protected ?string $scope = null;

    /**
     * Token constructor.
     *
     * @param UserInterface $user
     * @param string|null $token
     * @param bool $isAuthenticated
     */
    public function __construct(
        protected UserInterface $user,
        protected ?string $token = null,
        protected bool $isAuthenticated = false,
    ) {
        $this->token ??= $this->generateToken();
        if (!isset($this->expires)) {
            $this->expires = (new DateTime())->modify('+ 5 hours');
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
    public function hasNotExpired(): bool {
        return $this->expires->getTimestamp() > time();
    }

    /**
     * @inheritDoc
     */
    public function expiresAt(): \DateTimeInterface {
        return $this->expires;
    }


    public function getTokenString(): string {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated(): bool {
        return $this->isAuthenticated;
    }

    /**
     * @inheritDoc
     */
    public function setIsAuthenticated( bool $isAuthenticated ): void {
        $this->isAuthenticated = $isAuthenticated;
    }

    /**
     * @inheritDoc
     */
    public function getData(): stdClass {
        $data = new stdClass();
        $data->id = $this->id;
        $data->accessToken = $this->token;
        $data->expires = $this->expiresAt();
        $data->clientId = $this->clientId;
        $data->userId = $this->userId;

        return $data;
    }

    /**
     * Generate a security token
     *
     * @return string
     */
    private function generateToken(): string {
        if (function_exists('random_bytes')) {
            $randomData = random_bytes(20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomData    = openssl_random_pseudo_bytes(20 );
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(20, MCRYPT_DEV_RANDOM);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        // Last resort which you probably should just get rid of:
        $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);

        return substr(hash('sha512', $randomData), 0, 40);
    }
}