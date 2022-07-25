<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;

use Swift\Orm\Mapping\Definition\Field;
use Swift\Orm\Dbal\TableQuery;

/**
 * Class Bool
 * @package Swift\Orm\Types
 */
final class Boolean implements TypeInterface {
    
    public const BOOL = 'bool';

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return sprintf( 'tinyint(%s)', $field->getLength() ?? 2 );
    }

    public function transformToPhpValue( mixed $value ): bool {
        return (bool) $value;
    }

    public function transformToDatabaseValue( mixed $value ): int {
        return (int) $value;
    }

    public function getName(): string {
        return self::BOOL;
    }
    
    public function getDatabaseType( \Swift\Orm\Mapping\Definition\Field $field ): string {
        return 'boolean';
    }


}