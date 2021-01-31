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
 * Class OauthJwt
 * @package Swift\Authorization\Model
 */
#[DB(table: 'oauth_jwt')]
class OauthJwt extends Entity {

    #[DBField(name: 'client_id', primary: true, type: FieldTypes::TEXT, length: 80, empty: false)]
    private string $clientId;

    #[DBField(name: 'subject', type: FieldTypes::TEXT, length: 80, empty: true)]
    private ?string $subject;

    #[DBField(name: 'public_key', type: FieldTypes::LONGTEXT, length: 2000, empty: false)]
    private string $publicKey;

}