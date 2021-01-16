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
use Swift\GraphQl\Types\ObjectType;
use Swift\Model\Entity;

class EntityInputGeneratorUpdate implements GeneratorInterface {

    /**
     * @inheritDoc
     */
    public function generate( ObjectType $type, TypeRegistry $typeRegistry ): Type {
        $typeDefinition = $typeRegistry->getTypeByClass($type->type);
        $fields = array();

        foreach ($typeDefinition->fields as $field) {
            $fields[$field->name] = $field->type === 'id' ? Type::nonNull($typeRegistry->createObject($field)) : $typeRegistry->createObject($field);
        }

        return new InputObjectType(array(
            'name' => $type->declaringMethod . ucfirst($type->name),
            'fields' => $fields,
        ));
    }
}