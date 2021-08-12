<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Generators;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\Type;
use ReflectionClass;
use Swift\GraphQl\TypeRegistry;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\ServiceLocator;
use Swift\Kernel\ServiceLocatorInterface;
use Swift\Model\Attributes\Field;
use Swift\Model\Entity;

/**
 * Class EntityEnumGenerator
 * @package Swift\GraphQl\Generators
 *
 * Generate enum for given Entity type
 */
class EntityEnumGenerator implements GeneratorInterface {

    private ServiceLocatorInterface $serviceLocator;
    private TypeRegistryInterface $inputTypeRegistry;
    private TypeRegistryInterface $outputTypeRegistry;

    /**
     * EntityArgumentGenerator constructor.
     */
    public function __construct() {
        $serviceLocator = new ServiceLocator();
        /** @var TypeRegistryInterface $this */
        $this->inputTypeRegistry = $serviceLocator->get(TypeRegistry\InputTypeRegistry::class);
        /** @var TypeRegistryInterface $this */
        $this->outputTypeRegistry = $serviceLocator->get(TypeRegistry\OutputTypeRegistry::class);
    }

    /**
     * @inheritDoc
     */
    public function generate( ObjectType $type, TypeRegistryInterface $typeRegistry ): Type {
        $classReflection = new ReflectionClass($type->type);

        if (!is_a(object_or_class: $type->type, class: Entity::class, allow_string: true)) {
            throw new \InvalidArgumentException(sprintf('%s only allows subclasses of %s, instead got: %s. Did you intend to use another generator?',
                static::class, Entity::class, $type->type));
        }

        $values = array();
        foreach ($classReflection->getProperties() as $reflectionProperty) {
            if (!empty($reflectionProperty->getAttributes(name: Field::class))) {
                $values[] = $reflectionProperty->getName();
            }
        }

        $object = $this->get($type->name) ?? new EnumType(array(
                'name' => ucfirst($type->name . 'Enum'),
                'description' => $type->description,
                'values' => $values,
            ));
        $this->inputTypeRegistry->getCompiled()->set($type->name, $object);

        return $object;
    }


    private function get( string $type ) {
        if ($this->inputTypeRegistry->getCompiled()->has($type)) {
            return $this->inputTypeRegistry->getCompiled()->get($type);
        }

        return $this->outputTypeRegistry->getCompiled()->has($type) ?
            $this->outputTypeRegistry->getCompiled()->get($type) : null;
    }
}