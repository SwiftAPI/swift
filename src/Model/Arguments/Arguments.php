<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Arguments;

use InvalidArgumentException;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;
use Swift\Model\Mapping\Table;
use Swift\Model\Query\QueryBuilder;
use TypeError;

/**
 * Class Arguments
 * @package Swift\Model\Arguments
 */
#[InputType]
class Arguments {

    /**
     * Arguments constructor.
     *
     * @param int|null    $offset
     * @param int|null    $limit
     * @param string|null $orderBy
     * @param string|null $groupBy
     * @param string|null $direction
     * @param array       $arguments
     */
    public function __construct(
        #[Field] public int|null $offset = 0,
        #[Field( defaultValue: 25 )] public int|null $limit = 0,
        #[Field] public string|null $orderBy = null,
        public string|null $groupBy = null,
        #[Field( type: ArgumentDirectionEnum::class )] public string|null $direction = null,
        private array $arguments = [],
    ) {
        if ( $this->direction && ! ArgumentDirectionEnum::isValid( $this->direction ) ) {
            throw new TypeError( sprintf( 'Expected one of the following types (%s) for argument $direction, instead got: %s', implode( separator: ', ', array: ArgumentDirectionEnum::keys() ), $this->direction ) );
        }
    }

    /**
     * @param \Swift\Model\Query\QueryBuilder $query
     * @param \Swift\Model\Mapping\Table      $table
     */
    public function apply( QueryBuilder $query, Table $table ): void {
        if ( $this->offset && ( $this->offset > 0 ) ) {
            $query->offset( $this->offset );
        }
        if ( $this->limit && ( $this->limit > 0 ) ) {
            $query->limit( $this->limit );
        }
        if ( $this->orderBy ) {
            if ( ! $table->hasFieldByPropertyName( $this->orderBy ) ) {
                throw new InvalidArgumentException(
                    sprintf( 'Field %s can not be used for ordering. The following options are available: %s',
                             $this->orderBy, implode( separator: ', ', array: array_map( static fn( Field $field ): string => $field->getName(), $table->getFields() ) )
                    ) );
            }
            $query->orderBy( $this->orderBy );
        }
        if ( $this->groupBy ) {
            if ( ! $table->hasFieldByPropertyName( $this->groupBy ) ) {
                throw new InvalidArgumentException(
                    sprintf( 'Field %s can not be used for grouping. The following options are available: %s',
                             $this->groupBy, implode( separator: ', ', array: array_keys( array_map( static fn( Field $field ): string => $field->getName(), $table->getFields() ) ) )
                    ) );
            }
            $query->groupBy( $this->groupBy );
        }
        if ( $this->direction === ArgumentDirectionEnum::ASC ) {
            $query->asc();
        }
        if ( $this->direction === ArgumentDirectionEnum::DESC ) {
            if ( ! $this->orderBy ) { // Fallback to primary key sorting
                $query->orderBy( $table->getPrimaryKey()?->getDatabaseName() );
            }
            $query->desc();
        }
        foreach ( $this->arguments as $argument ) {
            if ( ! is_a( $argument, ArgumentInterface::class, true ) ) {
                continue;
            }
            $query = $argument->apply( $query, $table );
        }
    }

    /**
     * Add argument
     *
     * @param ArgumentInterface $argument
     */
    public function addArgument( ArgumentInterface $argument ): void {
        $this->arguments[] = $argument;
    }

}