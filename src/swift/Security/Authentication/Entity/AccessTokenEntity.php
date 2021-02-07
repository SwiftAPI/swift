<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Entity;

use DateTime;
use Swift\Kernel\Attributes\DI;
use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Entity;
use Swift\Model\EntityInterface;
use Swift\Model\Types\FieldTypes;

/**
 * Class AccessTokenEntity
 * @package Swift\Authorization\Model
 */
#[DI(aliases: [EntityInterface::class . ' $accessTokenEntity']), DB(table: 'security_access_tokens')]
final class AccessTokenEntity extends Entity {

    #[DBField(name: 'id', primary: true, type: FieldTypes::INT, length: 11)]
    private int $id;

    #[DBField( name: 'access_token', type: FieldTypes::TEXT, length: 40, empty: false, unique: true )]
    private string $accessToken;

    #[DBField(name: 'client_id', type: FieldTypes::TEXT, length: 80, empty: false)]
    private string $clientId;

    #[DBField(name: 'user_id', type: FieldTypes::TEXT, length: 80, empty: true)]
    private ?string $userId;

    #[DBField(name: 'expires', type: FieldTypes::TIMESTAMP, empty: false)]
    private DateTime $expires;

    #[DBField(name: 'scope', type: FieldTypes::LONGTEXT, length: 4000, empty: true)]
    private ?string $scope;
}