<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Entity;

use Swift\Kernel\Attributes\DI;
use Swift\Model\Attributes\Field;
use Swift\Model\Attributes\Table;
use Swift\Model\Entity;
use Swift\Model\EntityInterface;
use Swift\Model\Types\FieldTypes;

/**
 * Class OauthClientsEntity
 * @package Swift\Security\Authentication\Entity
 */
#[DI( aliases: [ EntityInterface::class . ' $oauthClientsEntity', EntityInterface::class . ' $securityClientsEntity' ] ), Table( name: 'security_clients' )]
final class OauthClientsEntity extends Entity {

    #[Field( name: 'id', primary: true, type: FieldTypes::INT, length: 11 )]
    private int $id;

    #[Field( name: 'client_id', type: FieldTypes::TEXT, length: 80, empty: false, unique: true )]
    private string $clientId;

    #[Field( name: 'client_secret', type: FieldTypes::TEXT, length: 80, empty: true )]
    private ?string $clientSecret;

    #[Field( name: 'redirect_uri', type: FieldTypes::LONGTEXT, length: 2000, empty: true )]
    private string $redirectUri;

    #[Field( name: 'grant_types', type: FieldTypes::TEXT, length: 80, empty: true )]
    private string $grantTypes;

    #[Field( name: 'scope', type: FieldTypes::LONGTEXT, length: 4000, empty: true )]
    private string $scope;

    #[Field( name: 'created', type: FieldTypes::DATETIME, empty: false )]
    private \DateTime $created;

}