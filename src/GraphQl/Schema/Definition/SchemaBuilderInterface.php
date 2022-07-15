<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema\Definition;


use Swift\DependencyInjection\Attributes\DI;
use Swift\GraphQl\DependencyInjection\DiTags;
use Swift\GraphQl\Schema\Registry;

#[DI( tags: [ DiTags::GRAPHQL_SCHEMA_BUILDER ] )]
interface SchemaBuilderInterface {
    
    public function define( Registry $registry ): Registry;
    
}