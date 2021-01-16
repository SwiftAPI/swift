<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Users\Model;


use Swift\Model\Attributes\DB;
use Swift\Model\Attributes\DBField;
use Swift\Model\Entity;
use Swift\Model\Types\FieldTypes;

/**
 * Class UserSettings
 * @package Honeywell\Model
 */
#[DB(table: 'user_settings')]
class UserSettings extends Entity {

    #[DBField(name: 'id', primary: true, type: FieldTypes::INT, length: 11)]
    private int $id;

    #[DBField(name: 'name', type: FieldTypes::TEXT, length: 255, unique: true)]
    private string $name;

    #[DBField(name: 'value', type: FieldTypes::TEXT)]
    private mixed $value;

    #[DBField(name: 'user_id', type: FieldTypes::INT, unique: true)]
    private int $userId;

}