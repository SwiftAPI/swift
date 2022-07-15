<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Tests\Executor\Resolver;


use GraphQL\Type\Definition\ResolveInfo;
use Swift\GraphQl\Attributes\Resolve;

class TestResolver extends \Swift\GraphQl\Executor\Resolver\AbstractResolver {
    
    #[Resolve( name: 'SayHello' )]
    public function resolveSomething( $objectValue, $args, $context, ResolveInfo $info ): mixed {
        return $objectValue;
    }
    
}