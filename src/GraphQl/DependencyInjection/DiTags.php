<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\DependencyInjection;


class DiTags {
    
    public const GRAPHQL_SCHEMA_GENERATOR = 'graphql.schema.generator';
    public const GRAPHQL_SCHEMA_BUILDER = 'swift.graphql.schema_builder';
    public const GRAPHQL_RESOLVER = 'swift.graphql.schema_resolver';
    public const GRAPHQL_RESOLVER_MIDDLEWARE = 'swift.graphql.schema_resolver_middleware';
    public const GRAPHQL_SCHEMA_MIDDLEWARE = 'swift.graphql.schema_middleware';
    
}