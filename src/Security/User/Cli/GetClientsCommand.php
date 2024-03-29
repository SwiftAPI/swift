<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Cli;


use Swift\Console\Command\AbstractCommand;
use Swift\Dbal\Exceptions\DatabaseException;
use Swift\Orm\Entity\Arguments;
use Swift\Orm\EntityManager;
use Swift\Security\User\Entity\OauthClientsEntity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GetClientsCommand
 * @package Swift\Security\User\Cli
 */
class GetClientsCommand extends AbstractCommand {
    
    /**
     * GetClientsCommand constructor.
     *
     * @param \Swift\Orm\EntityManager $entityManager
     */
    public function __construct(
        private readonly EntityManager $entityManager,
    ) {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'security:client:get';
    }
    
    /**
     * Search client(s) by name or id
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function execute( InputInterface $input, OutputInterface $output ): int {
        $state = [];
        
        if ( ! empty( $input->getOption( 'id' ) ) ) {
            $state[ 'id' ] = $input->getOption( 'id' );
        }
        if ( ! empty( $input->getOption( 'clientId' ) ) ) {
            $state[ 'clientId' ] = $input->getOption( 'clientId' );
        }
        
        try {
            $clients = $this->entityManager->findMany(
                OauthClientsEntity::class,
                $state,
                new Arguments(
                    ...[
                           'limit'  => $input->getOption( 'limit' ),
                           'offset' => $input->getOption( 'offset' ),
                       ]
                )
            );
            
            if ( ! empty( $clients ) ) {
                $this->io->writeln( '<fg=green>Clients</>' );
                $keys = $clients[ 0 ]->toArray();
                $this->io->table(
                    array_keys( $keys ),
                    array_map( static function ( $client ) use ( $keys ) {
                        $val = [];
                        
                        foreach ( $keys as $key => $value ) {
                            if ( $key === 'created' ) {
                                $val[ $key ] = $client->getCreated()->format( 'Y-m-d H:i:s' );
                                continue;
                            }
                            if ( $key === 'modified' ) {
                                $val[ $key ] = $client->getCreated()->format( 'Y-m-d H:i:s' );
                                continue;
                            }
                            if ( $key === 'uuid' ) {
                                $val[ $key ] = $client->getUuid()->toString();
                                continue;
                            }
                            if ( $key === 'accessTokens' ) {
                                $val[ $key ] = $client->accessTokens->count();
                                continue;
                            }
                            
                            $val[ $key ] = $client->{$key};
                        }
                        
                        return $val;
                    }, $clients->getIterator()->getArrayCopy() )
                );
            } else {
                
                $this->io->writeln( '<fg=yellow>No client(s) found</>' );
            }
        } catch ( DatabaseException $exception ) {
            $this->io->writeln( sprintf( '<fg=yellow>In %s line %s:</>', $exception->getFile(), $exception->getLine() ) );
            $this->io->error( $exception->getMessage() );
        }
        
        return 0;
    }
    
    protected function configure(): void {
        $this
            ->setDescription( 'Get client by name' )
            ->setHelp( 'Displays a client by name' )
            ->addOption( 'id', null, InputOption::VALUE_OPTIONAL, 'Find by id' )
            ->addOption( 'clientId', null, InputOption::VALUE_OPTIONAL, 'Find by name (clientId)' )
            ->addOption( 'limit', null, InputOption::VALUE_OPTIONAL, 'Limit max clients in result', 25 )
            ->addOption( 'offset', null, InputOption::VALUE_OPTIONAL, 'Offset clients in result', 0 );
    }
    
}