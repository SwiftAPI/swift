<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Entity;

use Ramsey\Uuid\UuidInterface;
use Swift\Orm\Attributes\Behavior\CreatedAt;
use Swift\Orm\Attributes\Behavior\UpdatedAt;
use Swift\Orm\Attributes\Behavior\Uuid\Uuid1;
use Swift\Orm\Attributes\Entity;
use Swift\Orm\Attributes\Field;
use Swift\Orm\Attributes\Index;
use Swift\Orm\Entity\AbstractEntity;
use Swift\Orm\Types\FieldTypes;

#[Entity( table: 'security_users_credentials' )]
#[Uuid1( field: 'uuid' )]
#[CreatedAt( field: 'created' )]
#[UpdatedAt( field: 'modified' )]
//#[Index( fields: [ 'credential', 'security_users_incrementId' ], unique: true )]
class UserCredentials extends AbstractEntity {
    
    #[Field( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
    protected int $id;
    
    #[Field( name: 'uuid', type: FieldTypes::UUID )]
    protected UuidInterface $uuid;
    
    #[Field( name: 'credential', type: FieldTypes::TEXT, length: 255 )]
    protected string $credential;
    
    #[Field( name: 'created', type: FieldTypes::DATETIME )]
    protected \DateTimeInterface $created;
    
    #[Field( name: 'modified', type: FieldTypes::DATETIME )]
    protected \DateTimeInterface $modified;
    
    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }
    
    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getUuid(): UuidInterface {
        return $this->uuid;
    }
    
    /**
     * @return string
     */
    public function getCredential(): string {
        return $this->credential;
    }
    
    /**
     * @param string $credential
     */
    public function setCredential( string $credential ): void {
        $this->credential = $credential;
    }
    
    /**
     * @return \DateTimeInterface
     */
    public function getCreated(): \DateTimeInterface {
        return $this->created;
    }
    
    /**
     * @param \DateTimeInterface $created
     */
    public function setCreated( \DateTimeInterface $created ): void {
        $this->created = $created;
    }
    
    /**
     * @return \DateTimeInterface
     */
    public function getModified(): \DateTimeInterface {
        return $this->modified;
    }
    
    /**
     * @param \DateTimeInterface $modified
     */
    public function setModified( \DateTimeInterface $modified ): void {
        $this->modified = $modified;
    }
    
}