<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\CoRoutines\Test;


use React\Promise\Promise;
use Swift\CoRoutines\Job;
use Swift\Console\Style\ConsoleStyle;
use function React\Async\async;
use function React\Async\await;

class TestCoRoutine2 implements \Swift\CoRoutines\CoRoutineInterface {
    
    public function getIdentifier(): string {
        return 'lorem-ipsum-dolor';
    }
    
    public function getDescription(): string {
        return 'Test cron number 2';
    }
    
    public function configure( Job $job ): Job {
        return $job->everyMinute()->onlyOne();
    }
    
    public function run( ?ConsoleStyle $consoleStyle ): void {
        async(function() use ( $consoleStyle ) {
            $consoleStyle?->writeln( 'Foo bar 2 here!' );
            await(\React\Promise\Timer\sleep( 2 ));
            $consoleStyle?->writeln( 'Doing cool things' );
            await(\React\Promise\Timer\sleep( 3 ));
            $consoleStyle?->writeln( 'Processed some more stuff!' );
        })();

    }
    
}