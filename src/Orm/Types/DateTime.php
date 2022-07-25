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
 * Class DateTime
 * @package Swift\Orm\Types
 */
final class DateTime implements TypeInterface {

    public const DATETIME = 'datetime';

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return 'datetime';
    }

    public function transformToPhpValue( mixed $value ): \DateTimeInterface {
        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        return new \DateTimeImmutable( $value );
    }

    public function transformToDatabaseValue( mixed $value ): string {
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        if (!is_string($value)) {
            throw new \RuntimeException('Invalid type detected; could not serialize');
        }

        return $value;
    }

    public function getName(): string {
        return self::DATETIME;
    }
    
    public function getDatabaseType( \Swift\Orm\Mapping\Definition\Field $field ): string {
        return 'datetime';
    }
    
}