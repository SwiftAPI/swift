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
use Swift\Model\EntityInterface;
use Swift\Model\Exceptions\DuplicateEntryException;
use Swift\Security\Authentication\Passport\Credentials\PasswordCredentialsEncoder;
use Swift\Security\User\Exception\UserAlreadyExistsException;

/**
 * Class UserProvider
 * @package Swift\Security\Authentication
 */
#[Autowire]
final class UserProvider implements UserProviderInterface {

    private UserInterface $user;

    /**
     * UserProvider constructor.
     *
     * @param UserStorageInterface $userDatabaseStorage
     */
    public function __construct(
        private UserStorageInterface $userDatabaseStorage,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getUserByUsername( string $username ): ?UserInterface {
        $userInfo = $this->userDatabaseStorage->findOne(array('username' => $username));

        if (!$userInfo) {
            return null;
        }

        return $this->getUserInstance($userInfo);
    }

    /**
     * @inheritDoc
     */
    public function getUserById( int $id ): ?UserInterface {
        $userInfo = $this->userDatabaseStorage->findOne(array('id' => $id));

        if (!$userInfo) {
            return null;
        }

        return $this->getUserInstance($userInfo);
    }

    /**
     * @inheritDoc
     */
    public function storeUser( string $username, string $password, string $email, string $firstname, string $lastname ): UserInterface {
        try {
            $data = $this->userDatabaseStorage->save([
                'username' => $username,
                'password' => (new PasswordCredentialsEncoder($password))->getEncoded(),
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'created' => new \DateTime(),
                'modified' => new \DateTime(),
            ]);
        } catch (DuplicateEntryException $exception) {
            throw new UserAlreadyExistsException($exception->getMessage());
        }

        return $this->getUserInstance($data);
    }

    /**
     * @param \stdClass $userInfo
     *
     * @return UserInterface
     */
    private function getUserInstance(\stdClass $userInfo): UserInterface {
        if (isset($this->user)) {
            return $this->user;
        }

        $this->user = new User(...(array)$userInfo);
        $this->user->setUserStorage($this->userDatabaseStorage);

        return $this->user;
    }


}