<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

use GraphQL\Error\DebugFlag;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use Psr\Http\Server\RequestHandlerInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\GraphQl\DependencyInjection\DiTags;
use Swift\GraphQl\Validator\Rules\QueryComplexityRateLimiter;
use Swift\HttpFoundation\JsonResponse;

/**
 * Class Kernel
 * @package Swift\GraphQl
 */
#[Autowire]
final class Kernel implements RequestHandlerInterface {
    
    /**
     * @param ConfigurationInterface $configuration
     * @param \Swift\GraphQl\Factory $factory
     * @param iterable               $validationRulesFactories
     */
    public function __construct(
        private readonly ConfigurationInterface $configuration,
        private readonly Factory                $factory,
        
        #[Autowire( tag: DiTags::GRAPHQL_SCHEMA_VALIDATOR_RULES_FACTORY )]
        private readonly iterable               $validationRulesFactories,
    ) {
    }
    
    public function handle( \Psr\Http\Message\ServerRequestInterface $request ): JsonResponse {
        return new JsonResponse( $this->execute( $request ) );
    }
    
    private function execute( \Psr\Http\Message\ServerRequestInterface $request ): array {
        $debug = \Swift\Configuration\Utils::isDevModeOrDebug( $this->configuration ) ?
            DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE : DebugFlag::NONE;
        
        try {
            $result = GraphQL::executeQuery(
                schema:          $this->factory->createSchema(),
                source:          $request->getContent()->get( key: 'query' ) ?: null,
                variableValues:  $request->getContent()->get( key: 'variables' ) ?: null,
                validationRules: $this->getValidationRules( $request ),
            );
            
            return $result->toArray( $debug );
        } catch ( \Exception $exception ) {
            return [ 'errors' => [ FormattedError::createFromException( $exception, $debug ) ] ];
        }
    }
    
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    private function getValidationRules( \Psr\Http\Message\ServerRequestInterface $request ): array {
        if ( ! $this->configuration->get( 'graphql.enable_introspection', 'app' ) ) {
            DocumentValidator::addRule( new DisableIntrospection() );
        }
        if ( $complexity = $this->configuration->get( 'graphql.max_query_complexity', 'app' ) ) {
            DocumentValidator::addRule( new QueryComplexity( $complexity ) );
        }
        if ( $depth = $this->configuration->get( 'graphql.max_query_depth', 'app' ) ) {
            DocumentValidator::addRule( new QueryDepth( $depth ) );
        }
        foreach ($this->validationRulesFactories as $validationRulesFactory) {
            foreach ($validationRulesFactory->create( $request ) as $validationRule) {
                DocumentValidator::addRule( $validationRule );
            }
        }
        
        
        return DocumentValidator::allRules();
    }
    
}