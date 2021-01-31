<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Authorization\Model;


use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Entity;
use Swift\Model\Types\FieldTypes;

/**
 * Class OauthClient
 * @package Swift\Authentication\Model
 */
#[DB(table: 'oauth_clients')]
final class OauthClients extends Entity {

    #[DBField( name: 'client_id', primary: true, type: FieldTypes::TEXT, length: 80, empty: false )]
    private string $clientID;

    #[DBField(name: 'client_secret', type: FieldTypes::TEXT, length: 80, empty: true)]
    private ?string $clientSecret;

    #[DBField(name: 'redirect_uri', type: FieldTypes::LONGTEXT, length: 2000, empty: true)]
    private string $redirectUri;

    #[DBField(name: 'grant_types', type: FieldTypes::TEXT, length: 80, empty: true)]
    private string $grantTypes;

    #[DBField(name: 'scope', type: FieldTypes::LONGTEXT, length: 4000, empty: true)]
    private string $scope;

    #[DBField(name: 'user_id', type: FieldTypes::TEXT, length: 80, empty: true)]
    private string $userId;

}