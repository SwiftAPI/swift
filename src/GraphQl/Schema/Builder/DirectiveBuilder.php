<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Builder;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Executor\Resolver;


class DirectiveBuilder extends TypeBuilder {
    
    private string|null $deprecationReason = null;
    
    /** @psalm-var callable(mixed, array<mixed>, mixed, ResolveInfo) : mixed|null */
    private $resolve;
    
    /** @psalm-var array<string, array<string, mixed>>|null */
    private array|null $args = null;
    
    /** @var \GraphQL\Language\DirectiveLocation[] */
    private array $locations = [];
    
    final private function __construct( string $name ) {
        parent::__construct( $name );
    }
    
    /**
     * @return static
     */
    public static function create( string $name ): self {
        return new static( $name );
    }
    
    /**
     * @return $this
     */
    public function addArgument( string $name, Type|array|callable $type, ?string $description = null, mixed $defaultValue = null ): self {
        if ( $this->args === null ) {
            $this->args = [];
        }
        
        $value = [ 'type' => $type ];
        
        if ( $description !== null ) {
            $value[ 'description' ] = $description;
        }
        
        if ( $defaultValue !== null ) {
            $value[ 'defaultValue' ] = $defaultValue;
        }
        
        $this->args[ $name ] = $value;
        
        return $this;
    }
    
    public function addLocation( string $location ): self {
        $this->locations[] = $location;
        
        return $this;
    }
    
    /**
     * @param callable(mixed, array<mixed>, mixed, ResolveInfo) : mixed $resolver
     *
     * @return $this
     * @see ResolveInfo
     *
     */
    public function setResolver( callable $resolver ): self {
        $this->resolve = $resolver;
        
        return $this;
    }
    
    /**
     * @return $this
     */
    public function setDeprecationReason( string $reason ): self {
        $this->deprecationReason = $reason;
        
        return $this;
    }
    
    /**
     * @return array<string, mixed>
     */
    public function build(): array {
        return [
            'args'              => $this->args,
            'name'              => $this->name,
            'description'       => $this->description,
            'deprecationReason' => $this->deprecationReason,
            'resolve'           => $this->resolve,
            'locations'         => $this->locations,
        ];
    }
    
    public function buildType(): Directive {
        return new Directive( $this->build() );
    }
    
}