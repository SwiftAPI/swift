<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console\Command;

use Swift\Console\Style\ConsoleStyle;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Kernel\KernelDiTags;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AbstractCommand
 * @package Swift\Console\Command
 */
#[DI(tags: [KernelDiTags::COMMAND]), Autowire]
abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command {

    /** @var ConsoleStyle $io Input/Output helper */
    protected ConsoleStyle $io;
    protected InputInterface $input;
    protected OutputInterface $output;

    private float $startTime = 0;

    /**
     * AbstractCommand constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setName($this->getCommandName());
    }

    /**
     * Return command name (the part after bin/console)
     *
     * @return string
     */
    abstract public function getCommandName(): string;

    /**
     * Runs the command.
     *
     * The code to execute is either defined directly with the
     * setCode() method or by overriding the execute() method
     * in a sub-class.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int The command exit code
     *
     * @throws \Exception When binding input fails. Bypass this by calling {@link ignoreValidationErrors()}.
     *
     * @see setCode()
     * @see execute()
     */
    public function run(InputInterface $input, OutputInterface $output): int {
        $this->io = new ConsoleStyle($input, $output);
        $this->input = $input;
        $this->output = $output;

        $this->beforeRun();

        $response = parent::run($input, $output);

        $this->afterRun();

        return $response;
    }

    /**
     * Get Symfony Style Command Helper
     *
     * @return ConsoleStyle
     */
    protected function getInputOutputHelper(): ConsoleStyle {
        return $this->io;
    }

    public function createOutputSection(): ConsoleSectionOutput {
        return $this->output->section();
    }

    /**
     * Before executing command
     */
    protected function beforeRun(): void {
        if ($this->input->getOption('track-time')) {
            $this->startTime = microtime(true);
        }
    }

    /**
     * After executing command
     */
    protected function afterRun(): void {
        if ($this->input->getOption('track-time')) {
            $this->io->note('Executed in ' . round((microtime(true) - $this->startTime), 2) . 's');
        }
    }

    protected function configure(): void {
        parent::configure();
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        return parent::execute( $input, $output );
    }



}