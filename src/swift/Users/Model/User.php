<?php declare(strict_types=1);

namespace Swift\Users\Model;

use JetBrains\PhpStorm\Deprecated;
use stdClass;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Entity\EntityManagerList;
use Swift\Model\Entity\EntityManagerSingle;
use Swift\Model\HenriModelBase;
use Swift\Users\Model\Entity\User as EntityUser;
use Swift\Users\Helper\User as HelperUser;

#[Autowire]
class User extends HenriModelBase {

	/**
	 * @var EntityUser  $entityUser
	 */
	private EntityUser $entityUser;

	/**
	 * @var HelperUser $helperUser
	 */
	private HelperUser $helperUser;

    /**
     * User constructor.
     *
     * @param EntityManagerSingle $entityManagerSingle
     * @param EntityManagerList $entityManagerList
     * @param EntityUser $entityUser
     * @param HelperUser $helperUser
     */
	public function __construct(
		EntityManagerSingle $entityManagerSingle,
		EntityManagerList $entityManagerList,
		EntityUser $entityUser,
		HelperUser $helperUser
	) {
		$this->entityUser   = $entityUser;
		$this->helperUser   = $helperUser;
		parent::__construct($entityManagerSingle, $entityManagerList);
	}

	/**
	 * Method to validate whether is user is allowed to log in
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function userMayLogin(string $username, string $password): bool {
		$user   = $this->entityUser->findOne(['username' => $username], true);

		if (!$this->helperUser->passwordCorrect($password, $user->password)) {
			// Password incorrect
			throw new \Exception('Password incorrect', 500);
		}

		return true;
	}

    /**
     * Method to create new user
     *
     * @param string $username
     * @param string $password
     * @param string $email
     *
     * @throws \Exception
     */
	public function createUser(string $username, string $password, string $email = ''): void {
		// Check if user does not exist already
		$user   = $this->entityUser->findOne(['username' => $username]);
		if (!is_null($user)) {
			// User already exists
			throw new \Exception('Username already taken', 500);
		}

		$this->entityUser->save([
		    'username' => $username,
            'password' => $this->helperUser->encryptPassword($password),
            'email' => $email,
        ]);
	}

	/**
	 * Method to get user by id
	 *
	 * @param int $userID
	 *
	 * @return stdClass|null
	 */
	public function getUserByID(int $userID): ?stdClass {
		$user       = $this->entityUser->findOne(['id' => $userID]);

		if (!$user) {
		    return null;
        }

		unset($user->password);

		return $user;
	}

	/**
	 * Method to get populated user from user entity
	 *
	 * @return stdClass
     *
     * @deprecated
	 */
	#[Deprecated(reason: 'Entity no longer support persistent state. Therefore this method no longer works')]
	public function getPopulatedUser(): stdClass {
		return $this->entityUser->getPropertiesAsObject();
	}
}