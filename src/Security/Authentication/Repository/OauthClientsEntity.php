<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Repository;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Class OauthClientsEntity
 * @package Swift\Security\Authentication\Entity
 */
#[Entity, Table(name: 'security_clients')]
final class OauthClientsEntity {

    #[Id, GeneratedValue, Column( name: 'id', type: 'integer', length: 11 )]
    private int $id;

    #[Column( name: 'client_id', type: 'string', length: 80, unique: true, nullable: false )]
    private string $clientId;

    #[Column( name: 'client_secret', type: 'string', length: 80, nullable: true )]
    private ?string $clientSecret;

    #[Column( name: 'redirect_uri', type: 'text', length: 2000, nullable: true )]
    private string $redirectUri;

    #[Column( name: 'grant_types', type: 'string', length: 80, nullable: true )]
    private string $grantTypes;

    #[Column( name: 'scope', type: 'text', length: 4000, nullable: true )]
    private string $scope;

    #[Column( name: 'created', type: 'datetime', nullable: false )]
    private \DateTimeInterface $created;

}