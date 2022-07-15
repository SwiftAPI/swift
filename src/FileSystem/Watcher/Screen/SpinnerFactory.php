<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\FileSystem\Watcher\Screen;

use AlecRabbit\Snake\Contracts\SpinnerInterface;
use AlecRabbit\Snake\Spinner;
use Swift\DependencyInjection\Attributes\DI;
use Symfony\Component\Console\Output\OutputInterface;

#[DI( autowire: false )]
final class SpinnerFactory {
    
    public static function create( OutputInterface $output, bool $spinnerDisabled ): SpinnerInterface {
        $hasColorSupport = $output->getFormatter()->isDecorated();
        if ( ! $hasColorSupport || $spinnerDisabled ) {
            return new VoidSpinner();
        }
        
        return new Spinner();
    }
    
}