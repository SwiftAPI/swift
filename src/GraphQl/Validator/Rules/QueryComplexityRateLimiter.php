<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Validator\Rules;


use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Validator\ValidationContext;

class QueryComplexityRateLimiter extends \GraphQL\Validator\Rules\QueryComplexity {
    
    public function __construct(
        protected \Psr\Http\Message\ServerRequestInterface         $request,
        protected \Swift\Security\RateLimiter\RateLimiterInterface $limiter,
    ) {
        parent::__construct( 999999 );
    }
    
    public function getVisitor( ValidationContext $context ) {
        $visitor = parent::getVisitor( $context );
        
        $visitor[ NodeKind::OPERATION_DEFINITION ][ 'leave' ] = function ( OperationDefinitionNode $operationDefinition ) use ( $visitor ) {
            // First call parent visitor
            $visitor[ NodeKind::OPERATION_DEFINITION ][ 'leave' ]( $operationDefinition );
            
            $complexity = $this->getQueryComplexity();
            $calculated = round( $complexity / 100 );
            $calculated = $calculated < 1 ? 1 : $calculated;
            
            $rate = $this->limiter->consume( (int) $calculated );
            $rate->denyIfNotAccepted();
        };
        
        return $visitor;
    }
    
    
}