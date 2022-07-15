<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Events\Command;


use Swift\Console\Command\AbstractCommand;
use Swift\DependencyInjection\ServiceLocatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListEventsCommand
 * @package Swift\Events\Command
 */
final class ListEventsCommand extends AbstractCommand {

    private array $events;

    /**
     * ListEventsCommand constructor.
     */
    public function __construct(
        private ServiceLocatorInterface $serviceLocator,
    ) {
        $this->events = $this->serviceLocator->getServicesByTag('events.event');
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        return 'events:list:all';
    }

    protected function configure(): void {
        $this
            ->setDescription('List available event(s)')
            ->setHelp('List all available events')
            ->addOption('--name', null, InputOption::VALUE_OPTIONAL, 'Full classname or shortname')
        ;
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $events = [];

        foreach ($this->events as $event) {
            $eventReflection = $this->serviceLocator->getReflectionClass($event);
            $events[$event] = [$event, $eventReflection->getStaticPropertyValue('eventDescription')];
        }

        ksort($events);

        $this->io->table(['event', 'description'], $events);

        return AbstractCommand::SUCCESS;
    }

}