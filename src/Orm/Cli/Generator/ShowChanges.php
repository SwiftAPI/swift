<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Cli\Generator;


use Cycle\Database\Schema\AbstractTable;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Spiral\Database\Schema\Comparator;
use Swift\Kernel\Utils\Environment;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowChanges implements GeneratorInterface {
    
    private array $changes;
    
    public function __construct(
        private readonly SymfonyStyle $output,
    ) {
    }
    
    /**
     * @param Registry $registry
     *
     * @return Registry
     */
    public function run( Registry $registry ): Registry {
        if (!Environment::isCli()) {
            return $registry;
        }
        
        $this->output->writeln( '<info>Detecting schema changes:</info>' );
        
        $this->changes = [];
        foreach ( $registry->getIterator() as $e ) {
            if ( $registry->hasTable( $e ) ) {
                $table = $registry->getTableSchema( $e );
                
                if ( $table->getComparator()->hasChanges() ) {
                    $this->changes[] = [
                        'database' => $registry->getDatabase( $e ),
                        'table'    => $registry->getTable( $e ),
                        'schema'   => $table,
                    ];
                }
            }
        }
        
        if ( $this->changes === [] ) {
            $this->output->writeln( '<fg=yellow>no database changes have been detected</fg=yellow>' );
            
            return $registry;
        }
        
        foreach ( $this->changes as $change ) {
            $this->output->write( sprintf( '• <fg=cyan>%s.%s</fg=cyan>', $change[ 'database' ], $change[ 'table' ] ) );
            $this->describeChanges( $change[ 'schema' ] );
        }
        
        return $registry;
    }
    
    /**
     * @param AbstractTable $table
     */
    protected function describeChanges( AbstractTable $table ): void {
        if ( ! $this->output->isVerbose() ) {
            $this->output->writeln(
                sprintf(
                    ': <fg=green>%s</fg=green> change(s) detected',
                    $this->numChanges( $table )
                )
            );
            
            return;
        }
        
        $this->output->write( "\n" );
        
        if ( ! $table->exists() ) {
            $this->output->writeln( '    - create table' );
        }
        
        if ( $table->getStatus() === AbstractTable::STATUS_DECLARED_DROPPED ) {
            $this->output->writeln( '    - drop table' );
            
            return;
        }
        
        $cmp = $table->getComparator();
        
        $this->describeColumns( $cmp );
        $this->describeIndexes( $cmp );
        $this->describeFKs( $cmp );
    }
    
    /**
     * @param AbstractTable $table
     *
     * @return int
     */
    protected function numChanges( AbstractTable $table ): int {
        $cmp = $table->getComparator();
        
        return count( $cmp->addedColumns() )
               + count( $cmp->droppedColumns() )
               + count( $cmp->alteredColumns() )
               + count( $cmp->addedIndexes() )
               + count( $cmp->droppedIndexes() )
               + count( $cmp->alteredIndexes() )
               + count( $cmp->addedForeignKeys() )
               + count( $cmp->droppedForeignKeys() )
               + count( $cmp->alteredForeignKeys() );
    }
    
    /**
     * @param Comparator $cmp
     */
    protected function describeColumns( Comparator $cmp ): void {
        foreach ( $cmp->addedColumns() as $column ) {
            $this->output->writeln( "    - add column <fg=yellow>{$column->getName()}</fg=yellow>" );
        }
        
        foreach ( $cmp->droppedColumns() as $column ) {
            $this->output->writeln( "    - drop column <fg=yellow>{$column->getName()}</fg=yellow>" );
        }
        
        foreach ( $cmp->alteredColumns() as $column ) {
            $column = $column[ 0 ];
            $this->output->writeln( "    - alter column <fg=yellow>{$column->getName()}</fg=yellow>" );
        }
    }
    
    /**
     * @param Comparator $cmp
     */
    protected function describeIndexes( Comparator $cmp ): void {
        foreach ( $cmp->addedIndexes() as $index ) {
            $index = implode( ', ', $index->getColumns() );
            $this->output->writeln( "    - add index on <fg=yellow>[{$index}]</fg=yellow>" );
        }
        
        foreach ( $cmp->droppedIndexes() as $index ) {
            $index = implode( ', ', $index->getColumns() );
            $this->output->writeln( "    - drop index on <fg=yellow>[{$index}]</fg=yellow>" );
        }
        
        foreach ( $cmp->alteredIndexes() as $index ) {
            $index = $index[ 0 ];
            $index = implode( ', ', $index->getColumns() );
            $this->output->writeln( "    - alter index on <fg=yellow>[{$index}]</fg=yellow>" );
        }
    }
    
    /**
     * @param Comparator $cmp
     */
    protected function describeFKs( Comparator $cmp ): void {
        foreach ( $cmp->addedForeignKeys() as $fk ) {
            $this->output->writeln( "    - add foreign key on <fg=yellow>{$fk->getColumn()}</fg=yellow>" );
        }
        
        foreach ( $cmp->droppedForeignKeys() as $fk ) {
            $this->output->writeln( "    - drop foreign key <fg=yellow>{$fk->getColumn()}</fg=yellow>" );
        }
        
        foreach ( $cmp->alteredForeignKeys() as $fk ) {
            $fk = $fk[ 0 ];
            $this->output->writeln( "    - alter foreign key <fg=yellow>{$fk->getColumn()}</fg=yellow>" );
        }
    }
    
    /**
     * @return bool
     */
    public function hasChanges(): bool {
        return $this->changes !== [];
    }
    
}