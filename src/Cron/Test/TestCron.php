<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cron\Test;

use Swift\Cron\Job;
use Swift\Console\Style\ConsoleStyle;

class TestCron implements \Swift\Cron\CronInterface {
    
    public function getIdentifier(): string {
        return 'foo-bar';
    }
    
    public function getDescription(): string {
        return 'Test cron';
    }
    
    public function configure( Job $job ): Job {
        return $job->everyMinute()->onlyOne();
    }
    
    public function run( ?ConsoleStyle $consoleStyle ): void {
        $consoleStyle?->writeln('Foo bar here!');
        sleep(3);
        $consoleStyle?->writeln('Hola hola hola');
        sleep(2);
        $consoleStyle?->writeln('Foo bar here ended!');
    }
    
}