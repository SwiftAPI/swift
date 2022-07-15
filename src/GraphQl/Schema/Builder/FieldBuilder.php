<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Builder;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Executor\Resolver;


class FieldBuilder {
    
    private string $name;
    
    private Type|\Closure $type;
    
    private string|null $description = null;
    
    private string|null $deprecationReason = null;
    
    protected array $isGranted = [];
    
    /** @psalm-var callable(mixed, array<mixed>, mixed, ResolveInfo) : mixed|null */
    private $resolve;
    
    /** @psalm-var array<string, array<string, mixed>>|null */
    private array|null $args = null;
    
    final private function __construct( string $name, Type|callable $type ) {
        $this->name = $name;
        $this->type = $type;
    }
    
    /**
     * @return static
     */
    public static function create( string $name, Type|callable $type ): self {
        return new static( $name, $type );
    }
    
    /**
     * @return $this
     */
    public function setDescription( string $description ): self {
        $this->description = $description;
        
        return $this;
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
     * @param array $isGranted
     *
     * @return \Swift\GraphQl\Schema\Builder\ConnectionBuilder
     */
    public function setIsGranted( array $isGranted ): self {
        $this->isGranted = $isGranted;
        
        return $this;
    }
    
    /**
     * @param mixed $grant
     *
     * @return $this
     */
    public function addIsGranted( mixed $grant ): self {
        $this->isGranted[] = $grant;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getIsGranted(): array {
        return $this->isGranted;
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
            'resolve'           => Resolver::wrapResolve( $this->resolve ),
            'type'              => $this->type,
            'auth'              => [
                'isGranted' => $this->isGranted,
            ],
        ];
    }
    
    public function buildType(): array {
        return $this->build();
    }
    
}