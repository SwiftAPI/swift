<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;



final class DoublePointValue implements TypeInterface {

    public const DOUBLE = 'double';

    public function transformToPhpValue( mixed $value ): float|null {
        return $value === null ? null : (double) $value;
    }

    public function transformToDatabaseValue( mixed $value ): float|null {
        return $value === null ? null : (double) $value;
    }

    public function getName(): string {
        return self::DOUBLE;
    }
    
    public function getDatabaseType( \Swift\Orm\Mapping\Definition\Field $field ): string {
        return 'double';
    }
    
}