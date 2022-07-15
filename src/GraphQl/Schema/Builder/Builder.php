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
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\Type;

class Builder {
    
    public static function enumType( string $name ): EnumBuilder {
        return EnumBuilder::create( $name );
    }
    
    public static function fieldType( string $name, \GraphQL\Type\Definition\Type|callable $type ): FieldBuilder {
        return FieldBuilder::create( $name, $type );
    }
    
    public static function connectionType( string $name, ObjectBuilder $builder ): ConnectionBuilder {
        return ConnectionBuilder::create( $name, $builder );
    }
    
    public static function inputField( string $name, mixed $type ): InputFieldBuilder {
        return InputFieldBuilder::create( $name, $type );
    }
    
    public static function inputObject( string $name ): InputObjectBuilder {
        return InputObjectBuilder::create( $name );
    }
    
    public static function interface( string $name ): InterfaceBuilder {
        return InterfaceBuilder::create( $name );
    }
    
    public static function objectType( string $name ): ObjectBuilder {
        return ObjectBuilder::create( $name );
    }
    
    public static function directive( string $name ): DirectiveBuilder {
        return DirectiveBuilder::create( $name );
    }
    
    public static function union( string $name ): UnionBuilder {
        return UnionBuilder::create( $name );
    }
    
    public static function listOf( mixed $type ): ListOfType {
        return Type::listOf( $type );
    }
    
    public static function nonNull( mixed $type ): \GraphQL\Type\Definition\NonNull {
        return Type::nonNull( $type );
    }
    
}