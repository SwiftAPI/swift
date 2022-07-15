<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Builder;


use GraphQL\Type\Definition\EnumType;
use Swift\GraphQl\Exception\InvalidArgument;
use Swift\GraphQl\Type\PhpEnumType;

class EnumBuilder extends TypeBuilder {
    
    /** @var mixed[][] */
    private array $values = [];
    
    private string $enumClassName;
    
  
    public function __construct( string $name ) {
        $this->enumClassName = $name;
        
        parent::__construct( PhpEnumType::baseName( $name ) );
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
    public function addValue( string $value, ?string $name = null, ?string $description = null ): self {
        $name ??= $value;
        if ( preg_match( self::VALID_NAME_PATTERN, $name ) !== 1 ) {
            throw InvalidArgument::invalidNameFormat( $name );
        }
        
        $enumDefinition = [ 'value' => $value ];
        if ( $description !== null ) {
            $enumDefinition[ 'description' ] = $description;
        }
        
        $this->values[ $name ] = $enumDefinition;
        
        return $this;
    }
    
    public function build(): array {
        $parameters             = parent::build();
        $parameters[ 'values' ] = $this->values;
        
        return $parameters;
    }
    
    public function buildType(): EnumType {
        if ( enum_exists( $this->enumClassName ) ) {
            return new PhpEnumType( $this->enumClassName );
        }
        
        return new EnumType( $this->build() );
    }
}