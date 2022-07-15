<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;


use Swift\Dbal\Exceptions\DuplicateEntryException;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\EntityManagerInterface;
use Swift\Security\Authentication\Passport\Credentials\PasswordCredentialsEncoder;
use Swift\Security\User\Entity\UserCredentials;
use Swift\Security\User\Entity\UserEntity;
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
     * @param \Swift\Orm\EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function getUserByUsername( string $username ): ?UserInterface {
        if ( ! $userInfo = $this->entityManager->findOne( UserEntity::class, [ 'username' => $username ] ) ) {
            return null;
        }
        
        return $this->getUserInstance( $userInfo );
    }
    
    /**
     * @inheritDoc
     */
    public function getUserByEmail( string $email ): ?UserInterface {
        if ( ! $userInfo = $this->entityManager->findOne( UserEntity::class, [ 'email' => $email ] ) ) {
            return null;
        }
        
        return $this->getUserInstance( $userInfo );
    }
    
    
    /**
     * @inheritDoc
     */
    public function getUserById( int $id ): ?UserInterface {
        if ( ! $userInfo = $this->entityManager->findOne( UserEntity::class, [ 'id' => $id ] ) ) {
            return null;
        }
        
        return $this->getUserInstance( $userInfo );
    }
    
    /**
     * @inheritDoc
     */
    public function storeUser( string $username, string $password, string $email, string $firstname, string $lastname ): UserInterface {
        try {
            $user = new UserEntity();
            $user->setUsername( $username );
            $user->setEmail( $email );
            $user->setFirstname( $firstname );
            $user->setLastname( $lastname );
            $user->getCredentials()->setCredential( ( new PasswordCredentialsEncoder( $password ) )->getEncoded() );
            
            $this->entityManager->persist( $user );
            $this->entityManager->run();
            
            return $this->getUserInstance( $user );
        } catch ( DuplicateEntryException $exception ) {
            throw new UserAlreadyExistsException( $exception->getMessage() );
        }
    }
    
    /**
     * @param \Swift\Security\User\Entity\UserEntity $userInfo
     *
     * @return UserInterface
     */
    private function getUserInstance( UserEntity $userInfo ): UserInterface {
        if ( isset( $this->user ) ) {
            return $this->user;
        }
        
        $this->user = User::fromUserEntity( $userInfo );
        $this->user->setUserStorage( $this->entityManager );
        $this->user->setUserEntity( $userInfo );
        
        return $this->user;
    }
    
    
}