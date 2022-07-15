<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;

use stdClass;
use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Dbal\TableQuery;

/**
 * Class Json
 * @package Swift\Orm\Types
 */
final class Json implements TypeInterface {

    public const JSON = 'json';

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return 'longtext';
    }

    public function transformToPhpValue( mixed $value ): ?stdClass {
        return ( new \Swift\Serializer\Json( $value ) )->modeObject()->unSerialize();
    }

    public function transformToDatabaseValue( mixed $value ): string {
        return ( new \Swift\Serializer\Json( $value ) )->serialize();
    }

    public function getName(): string {
        return self::JSON;
    }
    
    public function getDatabaseType(): string {
        return 'json';
    }
    
}