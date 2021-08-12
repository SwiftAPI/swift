<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Types;

use Swift\Kernel\TypeSystem\Enum;

/**
 * Class FieldTypes
 * @package Swift\Model\Types
 */
class FieldTypes extends Enum {

    public const UNKNOWN = 'unknown';

    public const VARCHAR = Varchar::VARCHAR;
    public const TEXT = Varchar::VARCHAR;
    public const STRING = Varchar::VARCHAR;

    public const LONGTEXT = LongText::LONGTEXT;

    public const INT = Integer::INT;

    public const FLOAT = FloatingPointValue::FLOAT;
    public const BIG_FLOAT = BigFloat::BIG_FLOAT;
    public const DOUBLE = DoublePointValue::DOUBLE;

    public const DATETIME = DateTime::DATETIME;
    public const TIME = Time::TIME;
    public const TIMESTAMP = TimeStamp::TIMESTAMP;

    public const JSON = Json::JSON;

    public const BOOL = Boolean::BOOL;

}