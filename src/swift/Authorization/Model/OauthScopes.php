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
 * Class OauthScopes
 * @package Swift\Authorization\Model
 */
#[DB(table: 'oauth_scopes')]
final class OauthScopes extends Entity {

    #[DBField(name: 'scope', primary: true, type: FieldTypes::TEXT, length: 80, empty: false)]
    private string $scope;

    #[DBField(name: 'is_default', type: FieldTypes::BOOL, empty: false)]
    private bool $isDefault;

}