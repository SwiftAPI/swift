<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Runtime\Cli;


use Swift\Console\Command\AbstractCommand;
use Swift\Console\Style\ConsoleStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleLogger {
    
    private ConsoleStyle $io;
    private AbstractCommand|AbstractRuntimeCommand $command;
    
    /**
     * @return \Symfony\Component\Console\Style\SymfonyStyle
     */
    public function getIo(): SymfonyStyle {
        return $this->io;
    }
    
    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     */
    public function setIo( SymfonyStyle $io ): void {
        $this->io = $io;
    }
    
    /**
     * @return \Swift\Console\Command\AbstractCommand|\Swift\Runtime\Cli\AbstractRuntimeCommand
     */
    public function getCommand(): AbstractCommand|AbstractRuntimeCommand {
        return $this->command;
    }
    
    /**
     * @param \Swift\Console\Command\AbstractCommand|\Swift\Runtime\Cli\AbstractRuntimeCommand $command
     */
    public function setCommand( AbstractCommand|AbstractRuntimeCommand $command ): void {
        $this->command = $command;
    }
    
    
    
}