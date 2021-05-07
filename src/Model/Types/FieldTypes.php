<?php declare(strict_types=1);
/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Types;

use Swift\Kernel\TypeSystem\Enum;

class FieldTypes extends Enum {

    public const STRING = 'varchar';
    public const TEXT = 'varchar';
    public const LONGTEXT = 'longtext';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const DATETIME = 'datetime';
    public const TIME = 'time';
    public const TIMESTAMP = 'timestamp';
    public const JSON = 'longtext';
    public const BOOL = 'boolean';

}