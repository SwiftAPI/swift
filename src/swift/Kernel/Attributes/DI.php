<?php declare(strict_types=1);


namespace Swift\Kernel\Attributes;

use Attribute;

/**
 * Class DI
 * @package Swift\Kernel\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class DI {

    /**
     * DI constructor.
     *
     * @param string|null $name
     * @param array $tags
     * @param bool $shared
     * @param bool $exclude
     * @param bool $autowire
     * @param array $aliases
     */
    public function __construct(
        public string|null $name = null,
        public array $tags = array(),
        public bool $shared = true,
        public bool $exclude = false,
        public bool $autowire = true,
        public array $aliases = array(),
    ) {
    }
}