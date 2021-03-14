<?php declare(strict_types=1);

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
use Swift\GraphQl\TypeRegistry;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\ServiceLocator;
use Swift\Kernel\ServiceLocatorInterface;
use Swift\Model\Entity;

/**
 * Class EntityInputGeneratorUpdate
 * @package Swift\GraphQl\Generators
 */
class EntityInputGeneratorUpdate implements GeneratorInterface {

    private ServiceLocatorInterface $serviceLocator;
    private TypeRegistryInterface $inputTypeRegistry;
    private TypeRegistryInterface $outputTypeRegistry;

    /**
     * EntityInputGeneratorNew constructor.
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
        $typeDefinition = $this->inputTypeRegistry->getTypeByClass($type->type);

        $fields = array();
        foreach ($typeDefinition->fields as $key => $field) {
            $compiled = $this->inputTypeRegistry->createObject($field);

            if ($type->isList) {
                $compiled = Type::listOf($compiled);
            }

            $fields[$field->name] = $compiled;
        }

        $name   = $type->declaringMethod . 'Input';
        $object = $this->inputTypeRegistry->getCompiled()->get( $name ) ?? new InputObjectType(array(
                'name' => ucfirst($name),
                'fields' => $fields,
                'alias'  => $type->name,
            ));
        $this->inputTypeRegistry->getCompiled()->set( $name, $object );

        return \Swift\GraphQl\Types\Type::nonNull($object);
    }

    private function get( string $type ): ?InputObjectType {
        if ( $this->inputTypeRegistry->getCompiled()->has( $type ) ) {
            return $this->inputTypeRegistry->getCompiled()->get( $type );
        }

        return $this->outputTypeRegistry->getCompiled()->has( $type ) ?
            $this->outputTypeRegistry->getCompiled()->get( $type ) : null;
    }
}