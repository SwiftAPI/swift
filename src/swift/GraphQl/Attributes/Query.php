<?php declare(strict_types=1);

namespace Swift\GraphQl\Attributes;

use Attribute;
use Swift\Kernel\Attributes\DI;

/**
 * Class Query
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_METHOD), DI(exclude: true)]
class Query {

    /**
     * Query constructor.
     *
     * @param string|null $name
     * @param mixed $type
     * @param bool $nullable
     * @param bool $isList
     * @param string|null $generator
     * @param array $generatorArguments
     */
    public function __construct(
        public string|null $name = null,
        public string|null $type = null,
        public bool $nullable = true,
        public bool $isList = false,
        public string|null $generator = null,
        public array $generatorArguments = array(),
    ) {
    }
}