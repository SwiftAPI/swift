<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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
     * @param string|null $description
     */
    public function __construct(
        public string|null $name = null,
        public string|null $type = null,
        public bool $nullable = true,
        public bool $isList = false,
        public string|null $generator = null,
        public array $generatorArguments = array(),
        public string|null $description = null,
    ) {
    }
}