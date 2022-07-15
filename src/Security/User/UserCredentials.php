<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;


class UserCredentials implements UserCredentialInterface {
    
    
    public function __construct(
        protected ?int $id,
        protected ?string $uuid,
        protected ?string $credential,
        protected ?\DateTimeInterface $created,
        protected ?\DateTimeInterface $modified,
    ) {
    }
    
    /**
     * @return int|null
     */
    public function getId(): ?int {
        return $this->id;
    }
    
    /**
     * @return string|null
     */
    public function getUuid(): ?string {
        return $this->uuid;
    }
    
    /**
     * @return string|null
     */
    public function getCredential(): ?string {
        return $this->credential;
    }
    
    /**
     * @return \DateTimeInterface|null
     */
    public function getCreated(): ?\DateTimeInterface {
        return $this->created;
    }
    
    /**
     * @return \DateTimeInterface|null
     */
    public function getModified(): ?\DateTimeInterface {
        return $this->modified;
    }
    
    public static function fromUserCredentials( \Swift\Security\User\Entity\UserCredentials $credentials ): self {
        return new self(
            $credentials->getId(),
            $credentials->getUuid()->toString(),
            $credentials->getCredential(),
            $credentials->getCreated(),
            $credentials->getModified(),
        );
    }
}