<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;



final class Json implements TypeInterface {

    public const JSON = 'json';

    public function transformToPhpValue( mixed $value ): ?\stdClass {
        return ( new \Swift\Serializer\Json( $value ) )->modeObject()->unSerialize();
    }

    public function transformToDatabaseValue( mixed $value ): string {
        return ( new \Swift\Serializer\Json( $value ) )->serialize();
    }

    public function getName(): string {
        return self::JSON;
    }
    
    public function getDatabaseType( \Swift\Orm\Mapping\Definition\Field $field ): string {
        return 'json';
    }
    
}