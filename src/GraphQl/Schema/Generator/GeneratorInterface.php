<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Generator;

use Swift\DependencyInjection\Attributes\DI;
use Swift\GraphQl\DependencyInjection\DiTags;

#[DI( tags: [ DiTags::GRAPHQL_SCHEMA_GENERATOR ] )]
interface GeneratorInterface {
    
    public function generate( \Swift\GraphQl\Schema\Registry $registry ): \Swift\GraphQl\Schema\Registry;
    
}