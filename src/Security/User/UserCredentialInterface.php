<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User;


interface UserCredentialInterface {
    
    public function getId(): ?int;
    
    public function getUuid(): ?string;
    
    public function getCredential(): ?string;
    
    public function getCreated(): ?\DateTimeInterface;
    
    public function getModified(): ?\DateTimeInterface;
    
}