<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Command;


use Swift\Console\Command\AbstractCommand;
use Swift\Model\EntityInterface;
use Swift\Model\Exceptions\DatabaseException;
use Swift\Security\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateClientCommand
 * @package Swift\Security\User\Command
 */
class CreateClientCommand extends AbstractCommand {

    /**
     * CreateClientCommand constructor.
     *
     * @param EntityInterface $securityClientsEntity
     */
    public function __construct(
        private EntityInterface $securityClientsEntity,
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
        $this->io->title('Creating new client');
        $this->io->writeln('<fg=blue>Clients are used to provide outside access using this API to other APIs.</>');
        $clientId = $this->io->ask('Client name', null, static function(string|null $value) {
            if (empty($value) || (strlen($value) < 5)) {
                throw new \RuntimeException(sprintf('Given client name "%s" is not valid. Client name should be at least 5 characters.', $value));
            }

            return $value;
        });
        $clientSecret = $this->io->ask('Client secret (skip to generate random =>)', Utils::randomToken());

        try {
            $client = $this->securityClientsEntity->save([
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'created' => new \DateTime(),
            ]);

            $client->created = $client->created->format('Y-m-d H:i:s');

            $this->io->writeln('<fg=green>Created client</>');
            $this->io->horizontalTable(array_keys((array) $client), [array_map( static fn( $value) => $value ?? '', (array) $client)]);
        } catch (DatabaseException $exception) {
            $this->io->writeln(sprintf('<fg=yellow>In %s line %s:</>', $exception->getFile(), $exception->getLine()));
            $this->io->error($exception->getMessage());
        }

        return 0;
    }

}