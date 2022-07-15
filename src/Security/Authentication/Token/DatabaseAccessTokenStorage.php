<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\EntityManager;
use Swift\Security\Authentication\Entity\AccessTokenEntity;
use Swift\Security\User\Entity\OauthClientsEntity;
use Swift\Security\User\ClientUser;
use Swift\Security\User\Entity\UserEntity;

/**
 * Class DatabaseAccessTokenStorage
 * @package Swift\Security\Authentication\Token
 */
#[DI( aliases: [ TokenStorageInterface::class . ' $databaseStorageInterface' ] ), Autowire]
final class DatabaseAccessTokenStorage implements TokenStorageInterface {
    
    private ?\Closure $initializer = null;
    private ?TokenInterface $token = null;
    
    /**
     * DatabaseTokenProvider constructor.
     *
     * @param \Swift\Orm\EntityManager $entityManager
     */
    public function __construct(
        private readonly EntityManager $entityManager,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function supports( TokenInterface $token ): bool {
        return true;
    }
    
    public function findOne( array $state ): AccessTokenEntity|null {
        return $this->entityManager->findOne( AccessTokenEntity::class, $state );
    }
    
    /**
     * @inheritDoc
     */
    public function getToken( string $accessToken = null ): ?TokenInterface {
        if ( $initializer = $this->initializer ) {
            $this->initializer = null;
            $this->token       = $initializer( $this );
        }
        
        return $this->token;
    }
    
    /**
     * @inheritDoc
     */
    public function setToken( ?TokenInterface $token = null ): void {
        if ( ! $token ) {
            return;
        }
        
        $accessTokenEntity = $token->getId() ? $this->entityManager->findByPk( AccessTokenEntity::class, $token->getId() ) : new AccessTokenEntity();
        $accessTokenEntity->setAccessToken( $token->getTokenString() );
        $accessTokenEntity->setExpires( $token->expiresAt() );
        
        if ( ! $token->getId() ) {
            if ( $token->getUser() instanceof ClientUser ) {
                $accessTokenEntity->setClient( $this->entityManager->findByPk( OauthClientsEntity::class, $token->getUser()->getId() ) );
            } else {
                $accessTokenEntity->setUser( $this->entityManager->findByPk( UserEntity::class, $token->getUser()->getId() ) );
            }
            
            $accessTokenEntity->setScope( $token->getScope() );
        }
        
        $this->entityManager->persist( $accessTokenEntity );
        $this->entityManager->run();
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