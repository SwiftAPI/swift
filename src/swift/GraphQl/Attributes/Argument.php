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
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Argument
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Argument {

    /**
     * Argument constructor.
     *
     * @param string|null $name argument name in the schema
     * @param string|array|null $type array of types will lead to a union
     * @param string|null $generator FQN of the generator class
     * @param array|null $generatorArguments Arguments to be passed to the generator
     */
    public function __construct(
        public string|null $name = null,
        public string|array|null $type = null,
        public string|null $generator = null,
        #[ArrayShape(shape: ['entity' => 'string'])] public array|null $generatorArguments = null,
    ) {
    }
}