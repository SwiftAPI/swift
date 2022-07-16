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
use Swift\Orm\EntityManager;
use Swift\Security\User\Entity\OauthClientsEntity;
use Swift\Security\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateClientCommand
 * @package Swift\Security\User\Cli
 */
class CreateClientCommand extends AbstractCommand {
    
    /**
     * CreateClientCommand constructor.
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
        return 'security:client:create';
    }
    
    public function execute( InputInterface $input, OutputInterface $output ): int {
        $this->io->title( 'Creating new client' );
        $this->io->writeln( '<fg=blue>Clients are used to provide outside access using this API to other APIs.</>' );
        $clientId     = $this->io->ask( 'Client name', null, static function ( string|null $value ) {
            if ( empty( $value ) || ( strlen( $value ) < 5 ) ) {
                throw new \RuntimeException( sprintf( 'Given client name "%s" is not valid. Client name should be at least 5 characters.', $value ) );
            }
            
            return $value;
        } );
        
        $clientSecret = $this->io->ask( 'Client secret (skip to generate random =>)', Utils::randomToken() );
        $redirectUri  = $this->io->ask( 'Redirect URI (skip to skip =>)', null );
        $grantTypes   = $this->io->ask( 'Grant types (space separated =>)', null );
        $scope = $this->io->ask( 'Scope (space separated =>)', null );
        
        $client = new OauthClientsEntity();
        $client->setClientId( $clientId );
        $client->setClientSecret( $clientSecret );
        $client->setRedirectUri( $redirectUri );
        $client->setGrantTypes( $grantTypes );
        $client->setScope( $scope );
        
        try {
            $this->entityManager->persist( $client );
            $this->entityManager->run();
            
            $data = $client->toArray();
            $data['created'] = $client->getCreated()->format( 'Y-m-d H:i:s' );
            $data['modified'] = $client->getModified()->format( 'Y-m-d H:i:s' );
            
            $this->io->writeln( '<fg=green>Created client</>' );
            $this->io->horizontalTable( array_keys( $data ), [ array_map( static fn( $value ) => $value ?? '', $data ) ] );
        } catch ( DatabaseException $exception ) {
            $this->io->writeln( sprintf( '<fg=yellow>In %s line %s:</>', $exception->getFile(), $exception->getLine() ) );
            $this->io->error( $exception->getMessage() );
        }
        
        return 0;
    }
    
}