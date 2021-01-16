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
     * @param string|null $name
     * @param string|null $type
     * @param string|null $generator
     * @param array|null $generatorArguments
     */
    public function __construct(
        public string|null $name = null,
        public string|null $type = null,
        public string|null $generator = null,
        #[ArrayShape(shape: ['entity' => 'string'])] public array|null $generatorArguments = null,
    ) {
    }
}