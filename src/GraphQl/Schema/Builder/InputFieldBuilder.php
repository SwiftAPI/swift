<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Builder;


use ReflectionProperty;

class InputFieldBuilder {
    
    private string $name;
    
    private mixed $type;
    
    private string|null $description = null;
    
    private mixed $defaultValue;
    
    final private function __construct( string $name, mixed $type ) {
        $this->name = $name;
        $this->type = $type;
    }
    
    /**
     * @return static
     */
    public static function create( string $name, mixed $type ): self {
        return new static( $name, $type );
    }
    
    /**
     * @return $this
     */
    public function setDefaultValue( mixed $defaultValue ): self {
        $this->defaultValue = $defaultValue;
        
        return $this;
    }
    
    /**
     * @return $this
     */
    public function setDescription( string $description ): self {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * @psalm-return array<string, mixed>
     */
    public function build(): array {
        $config = [
            'name'        => $this->name,
            'description' => $this->description,
            'type'        => $this->type,
        ];
        
        $property = new ReflectionProperty( $this, 'defaultValue' );
        $property->setAccessible( true );
        if ( $property->isInitialized( $this ) ) {
            /** @psalm-suppress MixedAssignment */
            $config[ 'defaultValue' ] = $this->defaultValue;
        }
        
        return $config;
    }
    
    public function buildType(): array {
        return $this->build();
    }
    
}