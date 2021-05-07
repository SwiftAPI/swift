<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

use Swift\Kernel\TypeSystem\Enum;

/**
 * Class GraphQlDiTags
 * @package Swift\GraphQl3
 */
final class GraphQlDiTags extends Enum {

    public const CLASS_SOURCES_PROVIDER = 'graphql.di.class_sources_provider';
    public const TYPE_MAPPER = 'graphql.di.type_mapper';
    public const QUERY_LOADER = 'graphql.di.query_loader';
    public const MUTATION_LOADER = 'graphql.di.mutation_loader';

    public const GRAPHQL_TYPE = 'graphql.type';
    public const GRAPHQL_INPUT_TYPE = 'graphql.input_type';
    public const GRAPHQl_QUERY = 'graphql.query';
    public const GRAPHQL_MUTATION = 'graphql.mutation';
    public const GRAPHQL_DIRECTIVE = 'graphql.directive';

}