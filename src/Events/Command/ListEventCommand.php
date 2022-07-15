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
 * Class ListEventCommand
 * @package Swift\Events\Command
 */
final class ListEventCommand extends AbstractCommand {

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
        return 'events:list:single';
    }

    protected function configure(): void {
        $this
            ->setDescription('Display event data')
            ->setHelp('Display event data')
            ->addOption('--name', null, InputOption::VALUE_REQUIRED, 'Full classname or shortname')
        ;
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $this->io->newLine();
        $name = $input->getOption('name');
        $event = null;
        $reflection = null;
        foreach ($this->events as $eventItem) {
            $eventReflection = $this->serviceLocator->getReflectionClass($eventItem);
            if (($eventItem === $name) || ($eventReflection->getShortName() === $name)) {
                $event = $eventItem;
                $reflection = $eventReflection;
                break;
            }
        }

        if (is_null($event)) {
            $this->io->error(sprintf('Event with name %s not found', $input->getOption('name')));
            return AbstractCommand::SUCCESS;
        }

        $description = strlen($reflection?->getStaticPropertyValue('eventLongDescription')) > 0 ?
            $reflection?->getStaticPropertyValue('eventLongDescription') :
            $reflection?->getStaticPropertyValue('eventDescription');

        $parameters = "";
        foreach ($reflection->getConstructor()->getParameters() as $key => $parameter) {
            if ($key > 0) {
                $parameters .= " | ";
            }
            $parameters .= $parameter->allowsNull() ? '?' : '';
            if ($parameter->getType() instanceof \ReflectionUnionType) {
                $types = array();
                foreach ($parameter?->getType()->getTypes() as $type) {
                    $types[] = $type->getName();
                }
                $parameters .= implode('|', $types);
            } else {
                $parameters .= $parameter->getType()?->getName();
            }
            $parameters .= ' $' . $parameter->getName();
        }

        $this->io->horizontalTable(array('event', 'description', 'parameters'), array(array(
            $event,
            $description,
            $parameters,
        )));

        return AbstractCommand::SUCCESS;
    }

}