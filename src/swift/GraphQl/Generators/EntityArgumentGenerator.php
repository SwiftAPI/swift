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
use InvalidArgumentException;
use Swift\GraphQl\TypeRegistry;
use Swift\GraphQl\Types\ObjectType;

class EntityArgumentGenerator implements GeneratorInterface {

    /**
     * @inheritDoc
     */
    public function generate( ObjectType $type, TypeRegistry $typeRegistry ): Type {
        if (empty($type->generatorArguments['entity'])) {
            throw new InvalidArgumentException(sprintf('Required generatorArgument "entity" (fqcn of entity belonging to EntityArgument) is missing in %s', $type->declaringClass));
        }
        $typeDefinition = $typeRegistry->getTypeByClass(name: $type->type);
        $entityDefinition = $typeRegistry->getTypeByClass(name: $type->generatorArguments['entity']);

        $fields = array();
        foreach ($typeDefinition->fields as $field) {
            $identifier = $field->type;
            $name = $field->name;
            if ($field->name === 'orderBy') {
                $test = clone $field;
                $identifier = $type->declaringMethod . ucfirst($field->name);
                $name = $field->name;
                $test->name = $type->declaringMethod . ucfirst($field->name);
                $test->generator = EntityEnumGenerator::class;
                $test->type = $type->generatorArguments['entity'];
                $fields[$name] = $typeRegistry->createObject(type: $test, identifier: $identifier);
                continue;
            }

            $fields[$name] = $typeRegistry->createObject(type: $field, identifier: $identifier);
        }

        // Add where column
        $entityFields = array();
        foreach ($entityDefinition->fields as $item) {
            $entityFields[$item->name] = Type::listOf($typeRegistry->createObject(type: $item));
        }
        $fields['where'] = new InputObjectType(array(
            'name' => 'where' . ucfirst($type->declaringMethod),
            'fields' => $entityFields,
        ));

        $object = $typeRegistry->getCompiledType(type: $type->declaringMethod . ucfirst($type->name)) ?? new InputObjectType(array(
            'name' => $type->declaringMethod . ucfirst($type->name),
            'fields' => $fields,
        ));

        $typeRegistry->definitions['types'][$object->name] = $object;

        return $object;
    }
}