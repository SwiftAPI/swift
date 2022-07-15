<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Builder;


use GraphQL\Type\Definition\InputObjectType;

class InputObjectBuilder extends TypeBuilder {
    
    /** @var list<array<string, mixed>>|callable():list<array<string, mixed>> */
    private $fields = [];
    
    /**
     * @return static
     */
    public static function create( string $name ): self {
        return new static( $name );
    }
    
    /**
     * @param list<array<string, mixed>>|callable():list<array<string, mixed>> $fields
     *
     * @return $this
     */
    public function setFields( callable|array $fields ): self {
        $this->fields = $fields;
        
        return $this;
    }
    
    public function addField( string $name, mixed $field ): self {
        if ( ! isset( $this->fields ) ) {
            $this->fields = [];
        }
        
        if ( ! is_array( $this->fields ) ) {
            $this->fields = [ $this->fields ];
        }
        
        $this->fields[ $name ] = $field;
        
        return $this;
    }
    
    /**
     * @psalm-return array<string, mixed>
     */
    public function build(): array {
        return [
            'name'        => $this->name,
            'description' => $this->description,
            'fields'      => $this->fields,
        ];
    }
    
    public function buildType(): InputObjectType {
        return new InputObjectType( $this->build() );
    }
    
}