<?php declare(strict_types=1);


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
    public const JSON = 'longtext';

}