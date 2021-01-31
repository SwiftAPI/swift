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
 * Class OauthAccessTokens
 * @package Swift\Authentication\Model
 */
#[DB(table: 'oauth_access_tokens')]
final class OauthAccessTokens extends Entity {

    #[DBField(name: 'access_token', primary: true, type: FieldTypes::TEXT, length: 40, empty: false)]
    private string $accessToken;

    #[DBField(name: 'client_id', type: FieldTypes::TEXT, length: 80, empty: false)]
    private string $clientId;

    #[DBField(name: 'user_id', type: FieldTypes::TEXT, length: 80, empty: true)]
    private ?string $userId;

    #[DBField(name: 'expires', type: FieldTypes::TIMESTAMP, empty: false)]
    private string $expires;

    #[DBField(name: 'scope', type: FieldTypes::LONGTEXT, length: 4000, empty: true)]
    private ?string $scope;

}