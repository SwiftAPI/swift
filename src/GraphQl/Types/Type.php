<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Types;

use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type as GraphQlType;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Type
 * @package Swift\GraphQl\Types
 */
class Type extends GraphQlType {

    public const STDCLASS = 'stdClass';
    public const STRING   = 'string';
    public const INT      = 'int';
    public const BOOLEAN  = 'boolean';
    public const BOOL     = 'bool';
    public const FLOAT    = 'float';
    public const ID       = 'id';
    public const DATETIME = 'DateTime';

    /**
     * Returns all builtin scalar types
     *
     * @return ScalarType[]
     */
    #[ArrayShape( [ self::ID => ScalarType::class, self::STRING => ScalarType::class, self::FLOAT => ScalarType::class, self::INT => ScalarType::class, self::BOOLEAN => ScalarType::class, self::STDCLASS => "string", self::DATETIME => "string" ] )]
    public static function getStandardTypes(): array {
        return [
            self::ID => static::id(),
            self::STRING => static::string(),
            self::FLOAT => static::float(),
            self::INT => static::int(),
            self::BOOLEAN => static::boolean(),
            self::BOOL => static::boolean(),
            self::STDCLASS => static::stdClass(),
            self::DATETIME => static::dateTime(),
        ];
    }

    /**
     * Returns all builtin scalar types
     *
     * @return ScalarType[]
     */
    #[ArrayShape( [ self::ID => ScalarType::class, self::STRING => ScalarType::class, self::FLOAT => ScalarType::class, self::INT => ScalarType::class, self::BOOLEAN => ScalarType::class, self::STDCLASS => "string", self::DATETIME => "string" ] )]
    public static function getStandardTypesClasses(): array {
        return [
            IDType::class => static::id(),
            StringType::class => static::string(),
            FloatType::class => static::float(),
            IntType::class => static::int(),
            BooleanType::class => static::boolean(),
            StdClassType::class => static::stdClass(),
            DateTimeType::class => static::dateTime(),
        ];
    }

    /**
     * @api
     */
    public static function stdClass(): ScalarType {
        if (! isset(static::$standardTypes[self::STDCLASS])) {
            static::$standardTypes[self::STDCLASS] = new StdClassType();
        }

        return static::$standardTypes[self::STDCLASS];
    }

    /**
     * @api
     */
    public static function dateTime(): ScalarType {
        if (! isset(static::$standardTypes[self::DATETIME])) {
            static::$standardTypes[self::DATETIME] = new DateTimeType();
        }

        return static::$standardTypes[self::DATETIME];
    }

}