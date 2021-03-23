<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console\Command;

use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Kernel\DiTags;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AbstractCommand
 * @package Swift\Console\Command
 */
#[DI(tags: [DiTags::COMMAND]), Autowire]
abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command {

    /** @var SymfonyStyle $io Input/Output helper */
    protected SymfonyStyle $io;

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
        $this->io = new SymfonyStyle($input, $output);

        return parent::run($input, $output);
    }

    /**
     * Get Symfony Style Command Helper
     *
     * @return SymfonyStyle
     */
    protected function getInputOutputHelper(): SymfonyStyle {
        return $this->io;
    }

}