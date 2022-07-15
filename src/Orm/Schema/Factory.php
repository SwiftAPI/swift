<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Schema;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\MergeIndexes;
use Cycle\Migrations;
use Cycle\Schema;
use Cycle\Schema\Compiler;
use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\Dbal\Dbal;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Mapping\ClassMetaDataFactory;
use Swift\Orm\Mapping\ClassLocator;

#[Autowire]
class Factory {
    
    private ?Migrations\Migrator $migrator = null;
    private ?Schema\Registry $registry = null;
    
    public function __construct(
        private readonly Dbal                                       $dbal,
        private readonly ClassLocator                               $classLocator,
        private readonly ConfigurationInterface                     $configuration,
        private readonly ClassMetaDataFactory                       $classMetaDataFactory,
        private readonly \Swift\Orm\Mapping\NamingStrategyInterface $namingStrategy,
        private readonly \Swift\Orm\Mapping\Driver\AttributeReader  $reader,
    ) {
    }
    
    protected function compileSchema(): array {
        return ( new Compiler() )->compile( new Schema\Registry( $this->dbal ), [
            new Embeddings( $this->classLocator ),
            new Entities( $this->classLocator ),
            new \Swift\Orm\Schema\Generator\Embeddings( $this->classLocator, $this->classMetaDataFactory, $this->namingStrategy, $this->reader ),
            new \Swift\Orm\Schema\Generator\Entities( $this->classMetaDataFactory, $this->namingStrategy, $this->reader ),
            new Schema\Generator\ResetTables(),
            new Schema\Generator\GenerateRelations(),
            new Schema\Generator\GenerateModifiers(),
            new Schema\Generator\ValidateEntities(),
            new Schema\Generator\RenderTables(),
            new Schema\Generator\RenderRelations(),
            new Schema\Generator\RenderModifiers(),
            new MergeIndexes(),
            new \Swift\Orm\Schema\Generator\MergeIndexes( $this->classMetaDataFactory, $this->namingStrategy, $this->reader ),
            new Schema\Generator\GenerateTypecast(),
        ] );
    }
    
    public function createSchema(): \Cycle\ORM\SchemaInterface {
        return new \Swift\Orm\Schema\Schema( $this->compileSchema() );
    }
    
    public function getMigrator(): ?Migrations\Migrator {
        if ( ! Utils::isDevMode( $this->configuration ) ) {
            return null;
        }
        
        if ( $this->migrator !== null ) {
            return $this->migrator;
        }
        
        $config = new Migrations\Config\MigrationConfig(
            [
                'directory' => INCLUDE_DIR . '/etc/orm/migrations/',  // where to store migrations
                'table'     => 'migrations'                      // database table to store migration status
            ]
        );
        
        $this->migrator = new Migrations\Migrator( $config, $this->dbal, new Migrations\FileRepository( $config ) );
        
        // Init migration table
        $this->migrator->configure();
        
        return $this->migrator;
    }
    
    public function getRegistry(): Schema\Registry {
        if ( is_null( $this->registry ) ) {
            $this->registry = new Schema\Registry( $this->dbal );
        }
        
        return $this->registry;
    }
    
}