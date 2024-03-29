<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema;


class Compiler {
    
    /**
     * @param \Swift\GraphQl\Schema\Registry                       $registry
     * @param \Swift\GraphQl\Schema\Generator\GeneratorInterface[] $generators
     *
     * @return \Swift\GraphQl\Schema\Registry
     */
    public function compile( Registry $registry, array $generators ): Registry {
        foreach ( $generators as $generator ) {
            $registry = $generator->generate( $registry );
        }
        
        $registry->build();
        
        return $registry;
    }
    
}