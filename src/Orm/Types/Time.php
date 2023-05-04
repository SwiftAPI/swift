<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;


final class Time implements TypeInterface {

    public const TIME = 'time';

    public function transformToPhpValue( mixed $value ): string {
        return (new \DateTime($value))->format('H:i:s');
    }

    public function transformToDatabaseValue( mixed $value ): string {
        return (new \DateTime($value))->format('H:i:s');
    }

    public function getName(): string {
        return self::TIME;
    }
    
    public function getDatabaseType( \Swift\Orm\Mapping\Definition\Field $field ): string {
        return 'time';
    }
    
}