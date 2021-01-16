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
use Swift\GraphQl\Types\ObjectType;
use Swift\Model\Attributes\DBField;
use Swift\Model\Entity;

/**
 * Class EntityEnumGenerator
 * @package Swift\GraphQl\Generators
 *
 * Generate enum for given Entity type
 */
class EntityEnumGenerator implements GeneratorInterface {

    /**
     * @inheritDoc
     */
    public function generate( ObjectType $type, TypeRegistry $typeRegistry ): Type {
        $classReflection = new ReflectionClass($type->type);

        if (!is_a(object_or_class: $type->type, class: Entity::class, allow_string: true)) {
            throw new \InvalidArgumentException(sprintf('%s only allows subclasses of %s, instead got: %s. Did you intend to use another generator?',
                static::class, Entity::class, $type->type));
        }

        $values = array();
        foreach ($classReflection->getProperties() as $reflectionProperty) {
            if (!empty($reflectionProperty->getAttributes(name: DBField::class))) {
                $values[] = $reflectionProperty->getName();
            }
        }

        $object = $typeRegistry->getCompiledType(type: $type->name) ?? new EnumType(array(
                'name' => $type->name,
                'values' => $values,
            ));

        $typeRegistry->definitions['types'][$object->name] = $object;

        return $object;
    }
}