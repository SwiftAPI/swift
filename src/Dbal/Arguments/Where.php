<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Arguments;

use Cycle\Database\Injection\Parameter;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Orm\Mapping\Definition\Entity;

/**
 * Class Where
 * @package Swift\Orm\Arguments
 */
#[DI( autowire: false )]
class Where implements ArgumentInterface {
    
    public const GREATER_THAN = '>';
    public const LESS_THAN    = '<';
    public const EQUALS       = '=';
    public const LIKE         = 'LIKE';
    public const CONTAINS     = 'CONTAINS';
    public const IN           = 'IN';
    
    private readonly ArgumentComparison $comparison;
    
    /**
     * Where constructor.
     *
     * @param string                                               $fieldName
     * @param \Swift\Dbal\Arguments\ArgumentComparison|string $comparison
     * @param mixed                                                $value
     */
    public function __construct(
        private readonly string        $fieldName,
        ArgumentComparison|string $comparison,
        private readonly mixed         $value,
    ) {
        $this->comparison = is_string( $comparison ) ? ArgumentComparison::from( $comparison ) : $comparison;
    }
    
    public static function compareAs( string $comparison ): ArgumentComparison {
        return ArgumentComparison::from( $comparison );
    }
    
    /**
     * Apply query
     *
     * @param \Cycle\ORM\Select                    $query
     * @param \Swift\Orm\Mapping\Definition\Entity $entity
     *
     * @return \Cycle\ORM\Select
     */
    public function apply( \Cycle\ORM\Select $query, Entity $entity ): \Cycle\ORM\Select {
        $value      = $this->value;
        $comparison = $this->comparison->value;
        
        if ( $this->comparison->value === static::CONTAINS ) {
            $value      = '%' . $value . '%';
            $comparison = self::LIKE;
        }
        if ( $this->comparison->value === static::IN ) {
            return $query->where(
                $this->fieldName,
                $comparison,
                new Parameter( is_array( $value ) ? $value : [ $value ] ),
            );
        }
        
        return $query->where(
            $this->fieldName,
            $comparison,
            $value
        );
    }
    
}