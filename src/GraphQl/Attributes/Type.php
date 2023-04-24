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
use JetBrains\PhpStorm\Deprecated;
use Swift\DependencyInjection\Attributes\DI;

/**
 * Class Type
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
#[DI(exclude: true)]
#[Deprecated]
#[\AllowDynamicProperties]
class Type {
    
    /**
     * Type constructor.
     *
     * @param string|null $name
     * @param string|null $extends
     * @param string|null $generator
     * @param array $generatorArguments
     * @param string|null $description
     */
    public function __construct(
        public ?string $name = null,
        public ?string $extends = null,
        public ?string $generator = null,
        public array $generatorArguments = array(),
        public string|null $description = null,
    ) {
    }
}