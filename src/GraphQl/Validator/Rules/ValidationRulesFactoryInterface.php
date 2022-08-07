<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Validator\Rules;

use Swift\DependencyInjection\Attributes\DI;
use Swift\GraphQl\DependencyInjection\DiTags;

#[DI( tags: [ DiTags::GRAPHQL_SCHEMA_VALIDATOR_RULES_FACTORY ] )]
interface ValidationRulesFactoryInterface {
    
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \GraphQL\Validator\Rules\ValidationRule[]
     */
    public function create( \Psr\Http\Message\ServerRequestInterface $request ): array;
    
}