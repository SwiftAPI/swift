<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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
     * @param array $tags
     * @param bool $shared
     * @param bool $exclude
     * @param bool $autowire
     * @param array $aliases
     */
    public function __construct(
        public array $tags = array(),
        public bool $shared = true,
        public bool $exclude = false,
        public bool $autowire = true,
        public array $aliases = array(),
    ) {
    }
}