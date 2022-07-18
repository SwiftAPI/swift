<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Executor;


use Cycle\ORM\SchemaInterface;
use GraphQL\Exception\InvalidArgument;
use Swift\Dbal\Arguments\ArgumentComparison;
use Swift\Dbal\Arguments\ArgumentDirection;
use Swift\Dbal\Arguments\Arguments;
use Swift\Dbal\Arguments\Where;
use Swift\Orm\Behavior\Listener\CreatedAt;
use Swift\Orm\Behavior\Listener\UpdatedAt;
use Swift\Orm\Behavior\Listener\Uuid\Uuid1;
use Swift\Orm\Behavior\Listener\Uuid\Uuid2;
use Swift\Orm\Behavior\Listener\Uuid\Uuid3;
use Swift\Orm\Behavior\Listener\Uuid\Uuid4;
use Swift\Orm\Behavior\Listener\Uuid\Uuid5;
use Swift\Orm\Behavior\Listener\Uuid\Uuid6;

class Utils {
    
    public static function extractAutomaticFieldsFromSchema( array $schema ): array {
        $exclude   = [];
        $listeners = $schema[ SchemaInterface::LISTENERS ] ?? [];
        
        $exclusions = [
            Uuid1::class,
            Uuid2::class,
            Uuid3::class,
            Uuid4::class,
            Uuid5::class,
            Uuid6::class,
            CreatedAt::class,
            UpdatedAt::class,
        ];
        
        foreach ( $listeners as $listener ) {
            if ( ! in_array( $listener[ 0 ], $exclusions, true ) ) {
                continue;
            }
            
            $exclude[] = $listener[ 1 ][ 'field' ] ?? null;
        }
        
        return $exclude;
    }
    
    public static function whereArgsToOrmArgument( array $args ): Arguments {
        $arguments = new Arguments();
        
        if ( isset( $args[ 'first' ] ) ) {
            $arguments->setLimit( $args[ 'first' ] );
            if ( isset( $args[ 'after' ] ) ) {
                $arguments->setOffset( $args[ 'after' ] );
            }
        }
        if ( isset( $args[ 'last' ] ) ) {
            $arguments->setLimit( $args[ 'last' ] );
            $arguments->setDirection( ArgumentDirection::DESC );
            if ( ! isset( $args[ 'orderBy' ] ) ) {
                $arguments->setOrderBy( 'id', ArgumentDirection::DESC );
            }
            if ( isset( $args[ 'before' ] ) ) {
                $arguments->addArgument( new Where( 'id', ArgumentComparison::LESS_THAN, $args[ 'before' ] ) );
            }
        }
        if ( isset( $args[ 'where' ] ) ) {
            foreach ( $args[ 'where' ] as $argGroup ) {
                foreach ( $argGroup as $name => $value ) {
                    $val = $value[ 'value' ] ?? ( $value[ 'values' ] ?? throw new InvalidArgument( 'No value found for argument ' . $name ) );
                    $arguments->addArgument(
                        new Where( $name, $value[ 'compare' ], $val )
                    );
                }
            }
        }
        
        return $arguments;
    }
    
}