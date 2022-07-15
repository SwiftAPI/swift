<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Attributes;

use JetBrains\PhpStorm\Deprecated;
use Swift\DependencyInjection\Attributes\DI;

#[\Attribute( \Attribute::TARGET_METHOD )]
#[DI( autowire: false )]
#[Deprecated]
class Resolve {
    
    public function __construct(
        protected string $name,
    ) {
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
}