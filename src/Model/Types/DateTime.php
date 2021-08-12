<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Types;


use Swift\Model\Mapping\Field;
use Swift\Model\Query\TableQuery;

/**
 * Class DateTime
 * @package Swift\Model\Types
 */
class DateTime implements TypeInterface {

    public const DATETIME = 'datetime';

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return 'datetime';
    }

    public function transformToPhpValue( mixed $value ): \DateTime {
        if ($value instanceof \Dibi\DateTime) {
            return new \DateTime( $value->__toString() );
        }
        if ($value instanceof \DateTime) {
            return $value;
        }

        return new \DateTime( $value );
    }

    public function transformToDatabaseValue( mixed $value ): string {
        if (($value instanceof \Dibi\DateTime) || ($value instanceof \DateTime)) {
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
}