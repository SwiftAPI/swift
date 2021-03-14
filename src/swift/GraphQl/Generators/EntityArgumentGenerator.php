<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Generators;


use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use Swift\GraphQl\TypeRegistry;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\ServiceLocator;
use Swift\Kernel\ServiceLocatorInterface;

/**
 * Class EntityArgumentGenerator
 * @package Swift\GraphQl\Generators
 */
class EntityArgumentGenerator implements GeneratorInterface {

    private ServiceLocatorInterface $serviceLocator;
    private TypeRegistryInterface $inputTypeRegistry;
    private TypeRegistryInterface $outputTypeRegistry;

    /**
     * EntityArgumentGenerator constructor.
     */
    public function __construct() {
        $serviceLocator = new ServiceLocator();
        /** @var TypeRegistryInterface $this */
        $this->inputTypeRegistry = $serviceLocator->get( TypeRegistry\InputTypeRegistry::class );
        /** @var TypeRegistryInterface $this */
        $this->outputTypeRegistry = $serviceLocator->get( TypeRegistry\OutputTypeRegistry::class );
    }

    /**
     * @inheritDoc
     */
    public function generate( ObjectType $type, TypeRegistryInterface $typeRegistry ): Type {
        if ( empty( $type->generatorArguments['entity'] ) ) {
            throw new InvalidArgumentException( sprintf( 'Required generatorArgument "entity" (fqcn of entity belonging to EntityArgument) is missing in %s', $type->declaringClass ) );
        }
        $typeDefinition   = $this->get( $type->type );
        $entityDefinition = $this->get( $type->generatorArguments['entity'] );

        $fields = array();
        foreach ( $typeDefinition->config['declaration']->fields as $field ) {
            $identifier = $field->type;
            $name       = $field->name;
            if ( $field->name === 'orderBy' ) {
                $test            = clone $field;
                $identifier      = $type->declaringMethod . ucfirst( $field->name );
                $name            = $field->name;
                $test->name      = $type->declaringMethod . ucfirst( $field->name );
                $test->generator = EntityEnumGenerator::class;
                $test->type      = $type->generatorArguments['entity'];
                $fields[ $name ] = $this->inputTypeRegistry->createObject( type: $test, identifier: $identifier );
                continue;
            }

            if ( array_key_exists( $field->type, \Swift\GraphQl\Types\Type::getStandardTypes() ) ) {
                $fields[ $name ] = \Swift\GraphQl\Types\Type::getStandardTypes()[ $field->type ];
                continue;
            }

            $fields[ $name ] = $this->inputTypeRegistry->createObject( type: $field, identifier: $identifier );
        }

        // Add where column
        $entityFields = array();
        foreach ( $entityDefinition->config['declaration']->fields as $item ) {
            $entityFields[ $item->name ] = Type::listOf( $this->inputTypeRegistry->createObject( type: $item ) );
        }
        $fields['where'] = new InputObjectType( array(
            'name'   => 'Where' . ucfirst( $type->declaringMethod ),
            'fields' => $entityFields,
        ) );

        $name   = $type->declaringMethod . ucfirst( $type->name ) . 'Input';
        $object = $this->inputTypeRegistry->getCompiled()->get( $name ) ?? new InputObjectType( array(
                'name'   => ucfirst($name),
                'fields' => $fields,
                'alias'  => $type->name,
            ) );
        $this->inputTypeRegistry->getCompiled()->set( $name, $object );

        return $object;
    }

    private function get( string $type ) {
        if ( $this->inputTypeRegistry->getCompiled()->has( $type ) ) {
            return $this->inputTypeRegistry->getCompiled()->get( $type );
        }

        return $this->outputTypeRegistry->getCompiled()->has( $type ) ?
            $this->outputTypeRegistry->getCompiled()->get( $type ) : null;
    }
}