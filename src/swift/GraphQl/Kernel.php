<?php declare( strict_types=1 );

namespace Swift\GraphQl;

use GraphQL\GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use Swift\Configuration\Configuration;
use Swift\GraphQl\Loaders\BatchLoader;
use Swift\GraphQl\Loaders\TypeLoader;
use Swift\GraphQl\Loaders\QueryLoader;
use Swift\GraphQl\Loaders\MutationLoader;
use Swift\GraphQl\Resolvers\FieldResolver;
use Swift\Http\Response\JSONResponse;
use Swift\Router\HTTPRequest;
use GraphQL\Error\DebugFlag;

/**
 * Class Kernel
 * @package Swift\GraphQl
 */
class Kernel {

    /**
     * Kernel constructor.
     *
     * @param HTTPRequest $request
     * @param Configuration $configuration
     * @param BatchLoader $batchLoader
     * @param TypeLoader $typeLoader
     * @param QueryLoader $queryLoader
     * @param MutationLoader $mutationLoader
     * @param TypeRegistry $typeRegistry
     * @param FieldResolver $fieldResolver
     */
    public function __construct(
        private HTTPRequest $request,
        private Configuration $configuration,
        private BatchLoader $batchLoader,
        private TypeLoader $typeLoader,
        private QueryLoader $queryLoader,
        private MutationLoader $mutationLoader,
        private TypeRegistry $typeRegistry,
        private FieldResolver $fieldResolver,
    ) {
    }

    public function run(): JSONResponse {
        $this->batchLoader->setLoaders(array(
            $this->typeLoader,
            $this->queryLoader,
            $this->mutationLoader,
        ));
        $this->batchLoader->load($this->typeRegistry);

        $this->typeRegistry->compile();
        //var_dump($this->typeRegistry);

        // @TODO: Compile type registry
        // @TODO: Generate root query
        // @TODO: Apply resolver logic

        return new JSONResponse( $this->execute() );
    }

    private function execute(): array {
        $fieldResolver = $this->fieldResolver;
        $schema = new Schema( array(
            'query' => $this->typeRegistry->getRootQuery(),
            'mutation' => $this->typeRegistry->getRootMutation(),
            ) );

        $schema->assertValid();

        $result = GraphQL::executeQuery(
            schema: $schema,
            source: $this->request->request->input->get( key: 'query' ) ?: null,
            variableValues: $this->request->request->input->get( key: 'variables' ) ?: null,
            fieldResolver: function ( $value, $args, $context, ResolveInfo $info) use ($fieldResolver) {
                return $fieldResolver->resolve($value, $args, $context, $info);
            }
        );

        $debug = ($this->configuration->get(settingName: 'app.debug', scope: 'root') || ($this->configuration->get(settingName: 'app.mode', scope: 'root') === 'develop')) ?
            DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE : DebugFlag::NONE;

        return $result->toArray($debug);
    }

}