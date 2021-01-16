<?php declare(strict_types=1);


namespace Swift\GraphQl;

use GraphQL\Type\Definition\EnumType;
use Swift\GraphQl\Exceptions\InvalidTypeException;
use Swift\GraphQl\Generators\GeneratorInterface;
use Swift\GraphQl\Types\ObjectType;
use GraphQL\Type\Definition\ObjectType as GraphQlObjectType;
use Swift\GraphQl\Types\Type;
use Swift\Kernel\TypeSystem\Enum;
use TypeError;

/**
 * Class TypeRegistry
 * @package Swift\GraphQl
 */
class TypeRegistry implements TypeRegistryInterface {

    /**
     * TypeRegistry constructor.
     *
     * @param array $definitions
     * @param ObjectType[] $types
     * @param ObjectType[] $extensions
     * @param ObjectType[] $queries
     * @param ObjectType[] $mutations
     * @param array $fqcnToNameMapping
     * @param GeneratorInterface[] $generators
     */
    public function __construct(
        public array $definitions = array(
            ObjectTypes::OUTPUT_TYPE => array(),
            ObjectTypes::INTERFACE => array(),
            ObjectTypes::INPUT_TYPE => array(),
            ObjectTypes::ENUM => array(),
            ObjectTypes::MUTATION => array(),
            ObjectTypes::QUERY => array(),
        ),
        private array $types = array(),
        private array $extensions = array(),
        private array $queries = array(),
        private array $mutations = array(),
        private array $fqcnToNameMapping = array(),
        private array $generators = array(),
    ) {
        foreach (Type::getStandardTypes() as $name => $type) {
//            $this->addType(new ObjectType(
//                name: $name,
//                declaringClass: $type::class,
//                type: $type::class,
//            ));
            $this->definitions[ObjectTypes::OUTPUT_TYPE][$name] = $type;
        }
    }

    /**
     * Add type declaration
     *
     * @param ObjectType $type
     *
     * @throws InvalidTypeException
     *
     */
    public function addType( ObjectType $type ): void {
        if (in_array(needle: $type->name, haystack: $this->types, strict: true)) {
            new InvalidTypeException(sprintf('Type with name %s has already been declared. Each type needs to have a unique name', $type->name));
        }

        $this->types[$type->name] = $type;
        $this->fqcnToNameMapping[$type->declaringClass] = $type->name;
    }

    /**
     * @param ObjectType[] $types
     *
     * @throws InvalidTypeException
     */
    public function addTypes( array $types ): void {
        foreach ($types as $type) {
            $this->addType($type);
        }
    }

    /**
     * @param string $name
     *
     * @return ObjectType|\GraphQL\Type\Definition\Type|null
     */
    public function getTypeByClass( string $name ): ObjectType|\GraphQL\Type\Definition\Type|null {
        if (array_key_exists(key: $name, array: Type::getStandardTypesClasses())) {
            return Type::getStandardTypesClasses()[$name];
        }
        if (array_key_exists(key: $name, array: Type::getStandardTypes())) {
            return Type::getStandardTypes()[$name];
        }

        return array_key_exists(key: $name, array: $this->fqcnToNameMapping) ? $this->types[$this->fqcnToNameMapping[$name]] : null;
    }

    /**
     * @return array
     */
    public function getTypes(): array {
        return $this->types;
    }

    /**
     * Add object type which extends another. Compile later when all types have been registered.
     *
     * @param ObjectType $type
     */
    public function addExtension( ObjectType $type ): void {
        $this->extensions[] = $type;
    }

    /**
     * @param ObjectType $type
     */
    public function addQuery( ObjectType $type ): void {
        if (in_array(needle: $type->name, haystack: $this->queries, strict: true)) {
            new InvalidTypeException(sprintf('Query with name %s has already been declared. Each type needs to have a unique name', $type->name));
        }

        $this->queries[$type->name] = $type;
        $this->fqcnToNameMapping[$type->declaringClass] = $type->name;
    }

    /**
     * @param string $name
     *
     * @return ObjectType|null
     */
    public function getQueryByName( string $name ): ObjectType|null {
        return $this->queries[$name] ?? null;
    }

    /**
     * @param ObjectType $type
     *
     * @return void
     */
    public function addMutation( ObjectType $type ): void {
        if (in_array(needle: $type->name, haystack: $this->mutations, strict: true)) {
            new InvalidTypeException(sprintf('Mutation with name %s has already been declared. Each type needs to have a unique name', $type->name));
        }

        $this->mutations[$type->name] = $type;
        $this->fqcnToNameMapping[$type->declaringClass] = $type->name;
    }

    /**
     * @param string $name
     *
     * @return ObjectType|null
     */
    public function getMutationByName( string $name ): ObjectType|null {
        return $this->mutations[$name] ?? null;
    }


    /**
     * @return GraphQlObjectType
     */
    public function getRootQuery(): GraphQlObjectType {
        $fields = array();

        foreach ($this->definitions['queries'] as $name => $query) {
            $fields[$name] = $query;
        }

        return new GraphQlObjectType(array(
            'name' => 'Query',
            'fields' => $fields,
        ));
    }

    /**
     * @return GraphQlObjectType
     */
    public function getRootMutation(): GraphQlObjectType {
        $fields = array();

        foreach ($this->definitions['mutations'] as $name => $mutation) {
            $fields[$name] = $mutation;
        }

        return new GraphQlObjectType(array(
            'name' => 'Mutation',
            'fields' => $fields,
        ));
    }


    /**
     * Compile added types
     */
    public function compile(): void {
        foreach ($this->queries as $query) {
            $objectType = array(
                'type' => $query->isList ? Type::listOf($this->createObject($this->getTypeByClass($query->type))) : $this->createObject($this->getTypeByClass($query->type)),
                'args' => $this->createArgs($query),
                'declaration' => $query,
            );
            $this->definitions['queries'][$query->name] = $objectType;
        }
        foreach ($this->mutations as $mutation) {
            $objectType = array(
                'name' => $mutation->name,
                'type' => $this->createObject($this->getTypeByClass($mutation->type)),
                'args' => $this->createArgs($mutation),
                'declaration' => $mutation,
            );
            $this->definitions['mutations'][$mutation->name] = $objectType;
        }
    }

    public function createObject(ObjectType|\GraphQL\Type\Definition\Type $type, ?string $identifier = null): \GraphQL\Type\Definition\Type {
        if ($type instanceof \GraphQL\Type\Definition\Type) {
            return $type;
        }

        $type->type ??= $type->declaringClass;
        $identifier ??= $type->type;

        if (!$type->generator && array_key_exists(key: $identifier, array: $this->definitions['types'])) {
            return $this->definitions['types'][$identifier];
        }

        $fields = array();
        foreach ($type->fields as $field) {
            if (!$field->type) {
                continue;
            }
            $field->type = $field->name === 'id' ? 'id' : $field->type;
            $fields[$field->name] = array_key_exists(key: $identifier, array: $this->definitions['types']) ?
                $this->definitions['types'][$identifier] : $this->createObject($field);
        }

        if ($type->generator) {
            if (!array_key_exists(key: $type->generator, array: $this->generators)) {
                $this->generators[$type->generator] = new $type->generator();
            }
            $generator = $this->generators[$type->generator];
            $object = $generator->generate($type, $this);
        } elseif (is_a(object_or_class: $identifier, class: Enum::class, allow_string: true)) {
            $object = new EnumType(array(
                'name' => $type->name,
                'values' => $identifier::keys(),
                'declaration' => $type,
            ));
            $this->definitions['types'][$identifier] = $object;
        } else {
            $object = new GraphQlObjectType(array(
                'name' => $type->name,
                'fields' => $fields,
                'declaration' => $type,
            ));
            $this->definitions['types'][$identifier] = $object;
        }

        return $object;
    }

    public function getCompiledType( string $type ) {
        if (array_key_exists(key: $type, array: $this->definitions['types'])) {
            return $this->definitions['types'][$type];
        }
    }

    private function createArgs( ObjectType|null $type ): array {
        $args = array();
        if (!$type || empty($type->args)) {
            return $args;
        }

        foreach ( $type->args as $arg ) {
            if (is_null($arg->type)) {
                throw new TypeError(sprintf('Could not resolve typing of argument in method "%s" in class "%s". This is required for GraphQl usage', $arg->name, $arg->declaringClass));
            }

            $resolved = null;

            if ($arg->generator || is_a(object_or_class: $arg->type, class: Enum::class, allow_string: true)) {
                $resolved = $this->createObject($arg);
            } else {
                $resolved = $this->getCompiledType(type: $arg->type);
            }
            $resolved = $arg->nullable ? $resolved : Type::nonNull($resolved);

            $args[$arg->name] = $resolved;
        }

        return $args;
    }


}