<?php declare(strict_types=1);


namespace Swift\GraphQl\Types;

use Swift\Kernel\Attributes\DI;

/**
 * Class ObjectType
 * @package Swift\GraphQl\Types
 */
#[DI(exclude: true)]
class ObjectType {

    /**
     * ObjectType constructor.
     *
     * @param string $name
     * @param string $declaringClass
     * @param string|null $declaringMethod
     * @param array $fields
     * @param string|null $resolve
     * @param array|null $args
     * @param string|null $type
     * @param bool $nullable
     * @param bool $isList
     * @param string|null $generator
     * @param array $generatorArguments
     */
    public function __construct(
        public string $name,
        public string $declaringClass,
        public string|null $declaringMethod = null,
        public array $fields = array(),
        public string|null $resolve = null,
        public array|null $args = null,
        public string|null $type = null,
        public bool $nullable = true,
        public bool $isList = false,
        public string|null $generator = null,
        public array $generatorArguments = array(),
    ) {
    }
}