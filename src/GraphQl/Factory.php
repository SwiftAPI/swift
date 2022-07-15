<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class Factory {
    
    public function __construct(
        protected \Swift\GraphQl\Schema\Factory $schemaFactory,
        protected \Swift\GraphQl\Executor\Resolver $resolver,
    ) {
    }
    
    public function createSchema(): \GraphQL\Type\Schema {
        return $this->schemaFactory->createSchema();
    }
    
    public function createResolver(): \Swift\GraphQl\Executor\Resolver {
        return $this->resolver;
    }
    
}