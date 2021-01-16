<?php declare(strict_types=1);


namespace Swift\GraphQl\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;

/**
 * Class Type
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS), DI(exclude: true)]
class Type {

    /**
     * Type constructor.
     *
     * @param string|null $name
     * @param string|null $extends
     * @param string|null $generator
     * @param array $generatorArguments
     */
    public function __construct(
        public ?string $name = null,
        public ?string $extends = null,
        public ?string $generator = null,
        public array $generatorArguments = array(),
    ) {
    }
}