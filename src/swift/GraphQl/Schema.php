<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

use Swift\Configuration\Configuration;
use Swift\GraphQl\Loaders\BatchLoader;
use Swift\GraphQl\Loaders\DirectivesLoader;
use Swift\GraphQl\Loaders\InputTypeLoader;
use Swift\GraphQl\Loaders\MutationLoader;
use Swift\GraphQl\Loaders\OutputTypeLoader;
use Swift\GraphQl\Loaders\QueryLoader;
use Swift\GraphQl\Resolvers\FieldResolver;
use Swift\GraphQl\TypeRegistry\DirectiveRegistry;
use Swift\GraphQl\TypeRegistry\MutationRegistry;
use Swift\GraphQl\TypeRegistry\QueryRegistry;
use Swift\HttpFoundation\RequestInterface;
use GraphQL\Type\Schema as GraphQlSchema;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class Schema
 * @package Swift\GraphQl
 */
#[Autowire]
class Schema {

    private GraphQlSchema $schema;

    /**
     * Kernel constructor.
     *
     * @param RequestInterface $request
     * @param Configuration $configuration
     * @param TypeRegistryPool $typeRegistryPool
     * @param BatchLoader $batchLoader
     * @param InputTypeLoader $inputTypeLoader
     * @param OutputTypeLoader $outputTypeLoader
     * @param QueryLoader $queryLoader
     * @param MutationLoader $mutationLoader
     * @param DirectivesLoader $directivesLoader
     * @param TypeRegistry $typeRegistry
     * @param FieldResolver $fieldResolver
     */
    public function __construct(
        private RequestInterface $request,
        private Configuration $configuration,
        private TypeRegistryPool $typeRegistryPool,
        private BatchLoader $batchLoader,
        private InputTypeLoader $inputTypeLoader,
        private OutputTypeLoader $outputTypeLoader,
        private QueryLoader $queryLoader,
        private MutationLoader $mutationLoader,
        private DirectivesLoader $directivesLoader,
        private TypeRegistry $typeRegistry,
        private FieldResolver $fieldResolver,
    ) {
    }

    public function compile(): void {
        $this->batchLoader->setLoaders( array(
            $this->inputTypeLoader,
            $this->outputTypeLoader,
            $this->queryLoader,
            $this->mutationLoader,
            $this->directivesLoader,
        ) );
        $this->batchLoader->load( $this->typeRegistry );

        $this->typeRegistryPool->compile();

        $this->typeRegistry->compile();

        $this->schema = new GraphQlSchema( array(
            'query'      => $this->typeRegistryPool->get( QueryRegistry::class )->getRootQuery(),
            'mutation'   => $this->typeRegistryPool->get( MutationRegistry::class )->getRootQuery(),
            'directives' => $this->typeRegistryPool->get( DirectiveRegistry::class )->getDirectives(),
        ) );

        $this->schema->assertValid();

        // @TODO: Cache schema and read it from there
//        $schema = BuildSchema::build(file_get_contents(INCLUDE_DIR . '/etc/schema.graphql'), function ($typeConfig, $typeDefinitionNode) {
//            var_dump($typeConfig);
//            return $typeConfig;
//        });
    }

    public function getSchema(): GraphQlSchema {
        if (!isset($this->schema)) {
            $this->compile();
        }

        return $this->schema;
    }

}