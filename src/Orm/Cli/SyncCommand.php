<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Cli;


use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\MergeIndexes;
use Cycle\Schema\Compiler;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\AbstractCommand;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\Cli\Generator\ShowChanges;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cycle\Schema;

#[Autowire]
class SyncCommand extends \Swift\Console\Command\AbstractCommand {
    
    public function __construct(
        private readonly \Swift\Dbal\DbalProvider                   $dbalProvider,
        private readonly \Swift\Orm\Mapping\ClassLocator            $classLocator,
        private readonly \Swift\Orm\Mapping\ClassMetaDataFactory    $classMetaDataFactory,
        private readonly \Swift\Orm\Mapping\NamingStrategyInterface $namingStrategy,
        private readonly \Swift\Orm\Mapping\Driver\AttributeReader  $reader,
    ) {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'orm:sync';
    }
    
    public function getDescription(): string {
        return 'Sync ORM schema with database (generate tables)';
    }
    
    public function getHelp(): string {
        return 'Sync ORM schema with database (generate tables)';
    }
    
    public function execute( InputInterface $input, OutputInterface $output ): int {
        $show = new ShowChanges( $this->io );
        
        $schema = ( new Compiler() )->compile( new Schema\Registry( $this->dbalProvider ), [
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
            $show,
            new Schema\Generator\SyncTables(),
        ] );
        
        if ( $show->hasChanges() ) {
            $this->io->writeln( "\n<info>ORM Schema has been synchronized</info>" );
        }
        
        return AbstractCommand::SUCCESS;
    }
    
}