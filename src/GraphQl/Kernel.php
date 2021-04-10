<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Validator\Rules\DisableIntrospection;
use Swift\Configuration\Configuration;
use Swift\Configuration\ConfigurationInterface;
use Swift\GraphQl\Resolvers\FieldResolver;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\RequestInterface;
use Swift\Kernel\Attributes\Autowire;
use GraphQL\Error\DebugFlag;

/**
 * Class Kernel
 * @package Swift\GraphQl
 */
#[Autowire]
class Kernel {

    /**
     * Kernel constructor.
     *
     * @param RequestInterface $request
     * @param ConfigurationInterface $configuration
     * @param Schema $schema
     * @param FieldResolver $fieldResolver
     */
    public function __construct(
        private RequestInterface $request,
        private ConfigurationInterface $configuration,
        private Schema $schema,
        private FieldResolver $fieldResolver,
    ) {
    }

    public function run(): JsonResponse {
        return new JsonResponse( $this->execute() );
    }

    private function execute(): array {
        $fieldResolver = $this->fieldResolver;

        $debug = ($this->configuration->get(identifier: 'app.debug', scope: 'root') || ($this->configuration->get(identifier: 'app.mode', scope: 'root') === 'develop')) ?
            DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE : DebugFlag::NONE;

        try {
            $result = GraphQL::executeQuery(
                schema: $this->schema->getSchema(),
                source: $this->request->getContent()->get( key: 'query' ) ?: null,
                variableValues: $this->request->getContent()->get( key: 'variables' ) ?: null,
                fieldResolver: function ( $value, $args, $context, ResolveInfo $info) use ($fieldResolver) {
                return $fieldResolver->resolve($value, $args, $context, $info);
            },
                validationRules: $this->getValidationRules()
            );

            return $result->toArray($debug);
        } catch(\Exception $exception) {
            return array('errors' => [FormattedError::createFromException($exception, $debug)]);
        }
    }

    /**
     * @return array
     */
    private function  getValidationRules(): array {
        $rules = array();

        if (!$this->configuration->get('graphql.enable_introspection', 'app')) {
            $rules[] = new DisableIntrospection();
        }

        return $rules;
    }

}