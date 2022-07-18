<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Dbal\Arguments;

use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;
use Swift\Orm\Mapping\Definition\Entity;

/**
 * Class Arguments
 * @package Swift\Orm\Arguments
 */
#[InputType]
class Arguments {
    
    #[Field( type: ArgumentDirection::class )] protected ArgumentDirection $direction;
    
    /**
     * Arguments constructor.
     *
     * @param int|null                                       $offset
     * @param int|null                                       $limit
     * @param string|null                                    $orderBy
     * @param string|null                                    $groupBy
     * @param \Swift\Dbal\Arguments\ArgumentDirection|string $direction
     * @param \Swift\Dbal\Arguments\ArgumentInterface[]      $arguments
     */
    public function __construct(
        #[Field] protected int|null $offset = 0,
        #[Field( defaultValue: 25 )] protected int|null $limit = 0,
        #[Field] protected string|null $orderBy = null,
        protected string|null $groupBy = null,
        ArgumentDirection|string $direction = ArgumentDirection::ASC,
        protected array $arguments = [],
    ) {
        $nativeArgs = [];
        $this->direction = $direction instanceof ArgumentDirection ? $direction : ArgumentDirection::from( $direction );
        if ( $this->offset && ( $this->offset > 0 ) ) {
            $nativeArgs[] = new Offset( $this->offset );
        }
        if ( $this->limit && ( $this->limit > 0 ) ) {
            $nativeArgs[] = new Limit( $this->limit );
        }
        if ( $this->orderBy ) {
            $nativeArgs[] = new OrderBy( $this->orderBy, $this->direction );
        }
        if ( $this->groupBy ) {
            $nativeArgs[] = new GroupBy( $this->groupBy );
        }
        
        $this->arguments = [
            ...$nativeArgs,
            ...$arguments,
        ];
    }
    
    /**
     * @param \Cycle\ORM\Select        $query
     * @param \Swift\Orm\Mapping\Definition\Entity $entity
     */
    public function apply( \Cycle\ORM\Select $query, Entity $entity ): void {
        foreach ( $this->arguments as $argument ) {
            if ( ! is_a( $argument, ArgumentInterface::class, true ) ) {
                continue;
            }
            $query = $argument->apply( $query, $entity );
        }
    }
    
    /**
     * @return int|null
     */
    public function getOffset(): ?int {
        return $this->offset;
    }
    
    /**
     * @param int|null $offset
     */
    public function setOffset( ?int $offset ): self {
        $this->offset = $offset;
        
        $didMatch = false;
        foreach ( $this->arguments as $argument ) {
            if ( is_a( $argument, Offset::class, true ) ) {
                $didMatch = true;
                $argument->setOffset( $offset ?? 0 );
            }
        }
        if ( ! $didMatch ) {
            $this->addArgument( new Offset( $offset ?? 0 ) );
        }
        
        return $this;
    }
    
    /**
     * Add argument
     *
     * @param ArgumentInterface $argument
     *
     * @return \Swift\Dbal\Arguments\Arguments
     */
    public function addArgument( ArgumentInterface $argument ): self {
        $this->arguments[] = $argument;
        
        return $this;
    }
    
    /**
     * @return int|null
     */
    public function getLimit(): ?int {
        return $this->limit;
    }
    
    /**
     * @param int|null $limit
     */
    public function setLimit( ?int $limit ): self {
        $this->limit = $limit;
        
        $didMatch = false;
        foreach ( $this->arguments as $argument ) {
            if ( is_a( $argument, Limit::class, true ) ) {
                $didMatch = true;
                $argument->setLimit( $limit ?? 0 );
            }
        }
        if ( ! $didMatch ) {
            $this->addArgument( new Limit( $limit ?? 0 ) );
        }
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getOrderBy(): ?string {
        return $this->orderBy;
    }
    
    /**
     * @param string|null                                 $orderBy
     * @param \Swift\Dbal\Arguments\ArgumentDirection $direction
     */
    public function setOrderBy( ?string $orderBy, ArgumentDirection $direction = ArgumentDirection::ASC ): self {
        $this->orderBy = $orderBy;
        $this->direction = $direction;
        
        $didMatch = false;
        foreach ( $this->arguments as $key => $argument ) {
            if ( is_a( $argument, OrderBy::class, true ) ) {
                $didMatch = true;
                $this->arguments[ $key ] = new OrderBy( $orderBy ?? '', $direction );
            }
        }
        if ( ! $didMatch ) {
            $this->addArgument( new OrderBy( $orderBy ?? '', $direction ) );
        }
        
        return $this;
    }
    
    /**
     * @param \Swift\Dbal\Arguments\ArgumentDirection $direction
     */
    public function setDirection( ArgumentDirection $direction ): self {
        $this->setOrderBy( $this->orderBy, $direction );
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getGroupBy(): ?string {
        return $this->groupBy;
    }
    
    /**
     * @param string|null $groupBy
     */
    public function setGroupBy( ?string $groupBy ): self {
        $this->groupBy = $groupBy;
        
        $didMatch = false;
        foreach ( $this->arguments as $argument ) {
            if ( is_a( $argument, GroupBy::class, true ) ) {
                $didMatch = true;
                $argument->setGroupBy( $groupBy ?? '' );
            }
        }
        if ( ! $didMatch ) {
            $this->addArgument( new GroupBy( $groupBy ?? '' ) );
        }
        
        return $this;
    }
    
    /**
     * @return \Swift\Dbal\Arguments\ArgumentInterface[]
     */
    public function getArguments(): array {
        return $this->arguments;
    }
    
    /**
     * @param \Swift\Dbal\Arguments\ArgumentInterface[] $arguments
     */
    public function setArguments( array $arguments ): self {
        $this->arguments = $arguments;
        
        return $this;
    }
    
    /**
     * @param \Swift\Dbal\Arguments\ArgumentInterface[] $arguments
     *
     * @return static
     */
    public static function fromArray( array $arguments ): self {
        return new self(
            0,
            0,
            null,
            null,
            ArgumentDirection::ASC,
            $arguments,
        );
    }
    
    
}