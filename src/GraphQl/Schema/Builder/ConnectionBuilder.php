<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Builder;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use Swift\GraphQl\Executor\Resolver;
use Swift\GraphQl\Relay\Relay;
use Swift\GraphQl\Schema\Registry;

class ConnectionBuilder extends TypeBuilder {
    
    /** @var InterfaceType[] */
    protected array $interfaces = [];
    
    /** @var callable():array<FieldDefinition|array<string, mixed>>|array<FieldDefinition|array<string, mixed>> */
    protected $fields = [];
    
    /** @var callable(mixed, array<mixed>, mixed, ResolveInfo) : mixed|null */
    protected $fieldResolver = null;
    
    protected array $isGranted = [];
    
    protected ObjectBuilder $builder;
    
    /**
     * @return static
     */
    public static function create( string $name, ObjectBuilder $builder ): self {
        return ( new static( $name ) )
            ->addInterface( Relay::CONNECTION, static fn() => Registry::$typeMap[ Relay::CONNECTION ] )
            ->setBuilder( $builder );
    }
    
    /**
     * @return $this
     */
    public function addInterface( string $name, InterfaceType|callable $interfaceType ): self {
        $this->interfaces[ $name ] = $interfaceType;
        
        return $this;
    }
    
    /**
     * @return \GraphQL\Type\Definition\InterfaceType[]
     */
    public function getInterfaces(): array {
        return $this->interfaces;
    }
    
    /**
     * @param callable():array<FieldDefinition|array<string, mixed>>|array<FieldDefinition|array<string, mixed>> $fields
     *
     * @return $this
     */
    public function setFields( callable|array $fields ): self {
        $this->fields = $fields;
        
        return $this;
    }
    
    /**
     * @return callable|array
     */
    public function getFields(): callable|array {
        return $this->fields;
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
     * @param \Swift\GraphQl\Schema\Builder\ObjectBuilder $builder
     *
     * @return \Swift\GraphQl\Schema\Builder\ConnectionBuilder
     */
    public function setBuilder( ObjectBuilder $builder ): self {
        $this->builder = $builder;
        
        return $this;
    }
    
    /**
     * @return \Swift\GraphQl\Schema\Builder\ObjectBuilder
     */
    public function getBuilder(): ObjectBuilder {
        return $this->builder;
    }
    
    /**
     * @param callable(mixed, array<mixed>, mixed, ResolveInfo) : mixed $fieldResolver
     *
     * @return $this
     */
    public function setFieldResolver( callable $fieldResolver ): self {
        $this->fieldResolver = $fieldResolver;
        
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
    
    public function build(): array {
        $parameters                   = parent::build();
        $parameters[ 'interfaces' ]   = $this->interfaces;
        $parameters[ 'fields' ]       = $this->fields;
        $parameters[ 'resolveField' ] = Resolver::wrapResolve( $this->fieldResolver );
        $parameters[ 'auth' ]         = [
            'isGranted' => $this->isGranted,
        ];
        
        return $parameters;
    }
    
    public function buildType(): mixed {
        return new ObjectType( $this->build() );
    }
    
    
}