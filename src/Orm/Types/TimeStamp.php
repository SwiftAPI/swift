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
 * Class TimeStamp
 * @package Swift\Orm\Types
 */
final class TimeStamp implements TypeInterface {

    public const TIMESTAMP = 'timestamp';

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return 'timestamp';
    }

    public function transformToPhpValue( mixed $value ): \DateTime {
        if ( $value instanceof \Dibi\DateTime ) {
            return new \DateTime( $value->__toString() );
        }

        if ( $value instanceof \DateTime ) {
            return $value;
        }

        return is_int( $value ) ? new \DateTime( date( 'Y-m-d H:i:s', $value ) ) : new \DateTime( $value );
    }

    public function transformToDatabaseValue( mixed $value ): mixed {
        if ( ( $value instanceof \Dibi\DateTime ) || ( $value instanceof \DateTime ) ) {
            return $value->format( 'Y-m-d H:i:s' );
        }

        return ( new \DateTime( $value ) )->format( 'Y-m-d H:i:s' );
    }

    public function getName(): string {
        return self::TIMESTAMP;
    }
    
    public function getDatabaseType( \Swift\Orm\Mapping\Definition\Field $field ): string {
        return 'timestamp';
    }
    
}