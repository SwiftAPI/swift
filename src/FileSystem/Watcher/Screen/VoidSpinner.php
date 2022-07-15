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

final class VoidSpinner implements SpinnerInterface {
    
    public function spin(): void {
    }
    
    public function interval(): float {
        return 1.0;
    }
    
    public function begin(): void {
    }
    
    public function end(): void {
    }
    
    public function erase(): void {
    }
    
    public function useStdOut(): void {
    }
    
}