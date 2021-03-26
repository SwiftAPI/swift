<?php declare(strict_types=1);

namespace Foo\Command;

use Swift\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FooCommand
 * @package Foo\Command
 */
class FooCommand extends AbstractCommand {

    /**
     * @inheritDoc
     */
    public function getCommandName(): string {
        // the name of the command (the part after "bin/console")
        return 'foo:bar';
    }

    /**
     * Configure command
     */
    protected function configure(): void {
        $this
            ->setDescription('Command description')

            ->setHelp('Explanatory information about command')
        ;
    }

    /**
     * @param InputInterface $input     Input for command
     * @param OutputInterface $output   Output helper for command
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->io->writeln('Foo bar');

        return AbstractCommand::SUCCESS; // OR AbstractCommand::FAILURE
    }

}