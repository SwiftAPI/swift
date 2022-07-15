<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\CoRoutines;

use Swift\Console\Style\ConsoleStyle;
use Swift\DependencyInjection\Attributes\DI;

#[DI( tags: [ CoRoutineDiTags::COROUTINE ] )]
interface CoRoutineInterface {
    
    /**
     * Refer to cron by identifier
     *
     * @return string
     */
    public function getIdentifier(): string;
    
    /**
     * Description for informational usage
     *
     * @return string
     */
    public function getDescription(): string;
    
    public function configure( Job $job ): Job;
    
    public function run( ?ConsoleStyle $consoleStyle ): void;
    
}