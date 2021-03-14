<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\TypeRegistry;


use GraphQL\Type\Definition\Type;
use Swift\GraphQl\Exceptions\DuplicateTypeException;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\ObjectType;
use Swift\Kernel\Attributes\DI;

/**
 * Class DirectiveRegistry
 * @package Swift\GraphQl\TypeRegistry
 */
#[DI(aliases: [TypeRegistryInterface::class . ' $directiveRegistry'])]
class DirectiveRegistry implements TypeRegistryInterface {

    private array $types = array();

    /**
     * @inheritDoc
     */
    public function addType( ObjectType $type ): void {
        if (array_key_exists($type->declaringClass, $this->types)) {
            throw new DuplicateTypeException(sprintf('Directive %s is already declared', $type->declaringClass));
        }

        $this->types[$type->declaringClass] = $type;
    }

    /**
     * @inheritDoc
     */
    public function addTypes( array $types ): void {
        array_map(fn($type) => $this->addType($type), $types);
    }

    /**
     * @inheritDoc
     */
    public function getTypeByClass( string $name ): ObjectType|Type|null {
        return $this->types[$name] ?? null;
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
     * @inheritDoc
     */
    public function compile(): void {}

    /**
     * @inheritDoc
     */
    public function addDirectives( array $directives ): void {
        $this->types = $directives;
    }

    /**
     * @inheritDoc
     */
    public function getDirectives(): array {
        return $this->types;
    }
}