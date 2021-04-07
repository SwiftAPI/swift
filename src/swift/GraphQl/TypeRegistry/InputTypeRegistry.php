<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\TypeRegistry;


use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use Swift\GraphQl\Exceptions\DuplicateTypeException;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\HttpFoundation\ParameterBag;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\ServiceLocator;
use Swift\Kernel\ServiceLocatorInterface;
use Swift\Kernel\TypeSystem\Enum;

/**
 * Class InputTypeRegistry
 * @package Swift\GraphQl\TypeRegistry
 */
#[DI( aliases: [ TypeRegistryInterface::class . ' $inputTypeRegistry' ] )]
class InputTypeRegistry implements TypeRegistryInterface {

    private array $generators = array();
    private array $definitions = array();
    private array $types = array();
    private ParameterBag $compiled;
    private TypeRegistryInterface $outputTypeRegistry;
    private ServiceLocatorInterface $serviceLocator;

    /**
     * InputTypeRegistry constructor.
     */
    public function __construct() {
        $this->serviceLocator = new ServiceLocator();
        /** @var TypeRegistryInterface */
        $this->outputTypeRegistry = $this->serviceLocator->get( OutputTypeRegistry::class );
    }

    /**
     * @inheritDoc
     */
    public function addTypes( array $types ): void {
        array_map( fn( $type ) => $this->addType( $type ), $types );
    }

    /**
     * @inheritDoc
     */
    public function addType( ObjectType $type ): void {
        if ( array_key_exists( $type->declaringClass, $this->types ) ) {
            throw new DuplicateTypeException( sprintf( 'Type %s is already declared', $type->declaringClass ) );
        }

        $this->types[ $type->declaringClass ] = $type;
    }

    /**
     * @inheritDoc
     */
    public function getTypeByClass( string $name ): ObjectType|Type|null {
        return $this->types[ $name ] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getTypes(): array {
        return $this->types;
    }

    /**
     * @inheritDoc
     */
    public function addExtension( ObjectType $type ): void {
        // TODO: Implement addExtension() method.
    }

    /**
     * @return ParameterBag
     */
    public function getCompiled(): ParameterBag {
        return $this->compiled ?? new ParameterBag();
    }

    /**
     * @inheritDoc
     */
    public function compile(): void {
        foreach ( $this->types as $type ) {
            $this->definitions[ $type->name ] = $this->createObject( $type );
        }

        $this->compiled = new ParameterBag( $this->definitions );
    }

    public function createObject( ObjectType|\GraphQL\Type\Definition\Type $type, string $identifier = null ) {
        if (!$type->generator && array_key_exists($type->type, $this->types)) {
            $type = $this->getTypeByClass($type->type);
        }

        if ( is_a( object_or_class: $type, class: \GraphQL\Type\Definition\Type::class, allow_string: false ) ) {
            return $type;
        }

        $type->type ??= $type->declaringClass;
        $identifier ??= $type->type;

        if ( ! $type->generator && array_key_exists( key: $identifier, array: $this->definitions ) ) {
            return $this->definitions[ $identifier ];
        }

        $fields = $this->resolveFields($type->fields ?? array());
        $args = $this->resolveFields($type->args ?? array());

        if ( $type->generator ) {
            if ( ! array_key_exists( key: $type->generator, array: $this->generators ) ) {
                $this->generators[ $type->generator ] = new $type->generator();
            }
            $generator = $this->generators[ $type->generator ];
            $object    = $generator->generate( $type, $this );
        } elseif ( array_key_exists( $identifier, \Swift\GraphQl\Types\Type::getStandardTypes() ) ) {
            $standardType = \Swift\GraphQl\Types\Type::getStandardTypes()[ $identifier ];
            return $type->nullable ? $standardType : \Swift\GraphQl\Types\Type::nonNull($standardType);
        } elseif ( is_a( object_or_class: $identifier, class: Enum::class, allow_string: true ) ) {
            $object = $this->outputTypeRegistry->createObject( $type ) ?? new EnumType( array(
                    'name'        => ucfirst($type->name),
                    'description' => $type->description,
                    'values'      => $identifier::keys(),
                    'declaration' => $type,
                ) );
            $this->definitions[ $identifier ] = $object;
        } else {
            $object = new InputObjectType( array(
                'name'        => ucfirst($type->name),
                'description' => $type->description,
                'fields'      => $fields,
                'declaration' => $type,
                'defaultValue' => $type->defaultValue,
            ) );
            $object = $type->nullable ? $object : \Swift\GraphQl\Types\Type::nonNull($object);
            $this->definitions[ $identifier ] = $object;
        }

        return $object;
    }

    private function resolveFields( array $items ): array {
        $fields = array();
        foreach ( $items as $field ) {
            if ( ! $field->type ) {
                continue;
            }

            $field->type = $field->name === 'id' ? 'id' : $field->type;
            $config = array(
                'description' => $field->description,
                'args' => $this->resolveFields($field->args ?? array()),
                'defaultValue' => $field->defaultValue,
            );
            if ( array_key_exists( $field->type, \Swift\GraphQl\Types\Type::getStandardTypes() ) ) {
                $config['type'] = $field->nullable ?
                    \Swift\GraphQl\Types\Type::getStandardTypes()[ $field->type ] :
                    \Swift\GraphQl\Types\Type::nonNull(\Swift\GraphQl\Types\Type::getStandardTypes()[ $field->type ]);
                $fields[ $field->name ] = $config;
                continue;
            }
            $config['type'] = array_key_exists( key: $field->type, array: $this->definitions ) ?
                $this->definitions[ $field->type ] : $this->createObject( $field );
            $fields[ $field->name ] = $config;
        }

        return $fields;
    }

}