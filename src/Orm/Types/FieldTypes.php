<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;

/**
 * Class FieldTypes
 * @package Swift\Orm\Types
 */
enum FieldTypes: string {

    case UNKNOWN = 'unknown';
    
    case TEXT = 'text';
    case LONGTEXT = 'longtext';

    case INT = 'int';

    case FLOAT = 'float';
    case BIG_FLOAT = 'big_float';
    case DOUBLE = 'double';

    case DATETIME = 'datetime';
    case TIME = 'time';
    case TIMESTAMP = 'timestamp';

    case JSON = 'json';

    case BOOL = 'bool';
    
    case UUID = 'uuid';
    
    case ENUM = 'enum';

}