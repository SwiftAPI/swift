<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Schema;


use GraphQL\Type\Schema;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\ServiceLocator;
use Swift\FileSystem\FileSystem;
use Swift\GraphQl\DependencyInjection\DiTags;
use Swift\GraphQl\Schema\Generator\BaseGenerator;
use Swift\GraphQl\Schema\Generator\BuilderGenerator;
use Swift\GraphQl\Schema\Generator\ManualGeneratorInterface;
use Swift\GraphQl\Schema\Middleware\SchemaMiddlewareExecutor;
use Swift\GraphQl\Relay\Schema\RelayBaseGenerator;
use Swift\Orm\GraphQl\Schema\Generator\OrmGenerator;

#[Autowire]
class Factory {
    
    protected array $generators = [];
    protected array $manualGenerators = [];
    
    public function __construct(
        protected FileSystem $fileSystem,
        protected SchemaMiddlewareExecutor $schemaMiddlewareExecutor,
    ) {
    }
    
    public function createSchema(): \GraphQL\Type\Schema {
        $registry = new Registry( $this->schemaMiddlewareExecutor );
        $registry = ( new Compiler() )->compile(
            $registry,
            [
                $this->manualGenerators[BaseGenerator::class],
                $this->manualGenerators[RelayBaseGenerator::class],
                $this->manualGenerators[OrmGenerator::class],
                $this->manualGenerators[BuilderGenerator::class],
                ...$this->generators
            ],
        );
        
        return new Schema(
            [
                'query'    => $registry->getType( 'Query' ),
                'mutation' => $registry->getType( 'Mutation' ),
                'types'    => $registry->getAll(),
                'directives' => $registry->getDirectives(),
            ],
        );
    }
    
    #[Autowire]
    public function setGenerators( #[Autowire( tag: DiTags::GRAPHQL_SCHEMA_GENERATOR )] iterable $generators ): void {
        if ( ! $generators ) {
            return;
        }
        
        $generators = iterator_to_array( $generators );
        
        foreach ( $generators as $generator ) {
            if ( $generator instanceof ManualGeneratorInterface ) {
                $this->manualGenerators[ $generator::class ] = $generator;
                continue;
            }
            $this->generators[] = $generator;
        }
    }
    
}