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
 * Class BigFloat
 * @package Swift\Orm\Types
 */
final class BigFloat implements TypeInterface {

    public const BIG_FLOAT = 'big_float';

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return sprintf('varchar(%s)', $field->getLength() ?? 128);
    }

    public function transformToPhpValue( mixed $value ): float {
        return (float) $value;
    }

    public function transformToDatabaseValue( mixed $value ): string {
        return (string) $value;
    }

    public function getName(): string {
        return self::BIG_FLOAT;
    }
    
    public function getDatabaseType(): string {
        return 'string(128)';
    }
    
    
}