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

class UnknownType implements TypeInterface {

    public function __construct(
        private string $name = 'unknown',
    ) {
        $test = new FieldTypes( $name );
    }

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return $this->name !== 'unknown' ? $this->name : sprintf( 'varchar(%s)', $field->getLength() ?? 255 );
    }

    public function transformToPhpValue( mixed $value ): mixed {
        return $value;
    }

    public function transformToDatabaseValue( mixed $value ): mixed {
        return $value;
    }

    public function getName(): string {
        return $this->name;
    }
}