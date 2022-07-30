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
use GraphQL\Validator\Rules\DisableIntrospection;
use Psr\Http\Server\RequestHandlerInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\DependencyInjection\Attributes\Autowire;
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
     */
    public function __construct(
        private readonly ConfigurationInterface $configuration,
        private readonly Factory                $factory,
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
                validationRules: $this->getValidationRules(),
            );
            
            return $result->toArray( $debug );
        } catch ( \Exception $exception ) {
            return [ 'errors' => [ FormattedError::createFromException( $exception, $debug ) ] ];
        }
    }
    
    /**
     * @return array
     */
    private function getValidationRules(): array {
        $rules = [];
        
        if ( ! $this->configuration->get( 'graphql.enable_introspection', 'app' ) ) {
            $rules[] = new DisableIntrospection();
        }
        
        return $rules;
    }
    
}