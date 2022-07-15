<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console;

use Swift\Console\Style\ConsoleStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Pretty print errors in console
 */
final class ErrorLogger {
    
    private readonly ConsoleStyle $consoleStyle;
    
    public function __construct(
        private readonly ArgvInput $input = new ArgvInput(),
        private readonly ConsoleOutput $output = new ConsoleOutput(),
    ) {
        $this->consoleStyle = new ConsoleStyle( $this->input, $this->output );
    }
    
    public function print( \Throwable $e ): void {
        while ( $e ) {
           $this->doLog( $e );
           $e = $e->getPrevious();
        }
    }
    
    private function doLog( \Throwable $e ): void {
        $this->consoleStyle->block( sprintf('Fatal error: Uncaught %s: %s in %s:%s', $e::class, $e->getMessage(), $e->getFile(), $e->getLine()), 'ERROR', 'error', '  ', true );
        
        $header = [
            '',
            'File',
            'Call',
        ];
        $trace = [];
        foreach ($e->getTrace() as $key => $item) {
            $item['args'] ??= [];
            foreach ($item['args'] as $argKey => $arg) {
                if (is_object($arg)) {
                    $item['args'][$argKey] = $arg::class;
                }
            }
            
            $trace[$key] = [];
            $trace[$key][] = $key + 1;
            $trace[$key][] = sprintf('%s:(%s)', $item['file']?? '', $item['line'] ?? '');
            $trace[$key][] = sprintf(
                '%s%s%s(%s)',
                $item['class'] ?? '',
                $item['type'] ?? '',
                is_array($item['function']) ? implode(', ', $item['function']) : $item['function'] ?? '',
                implode( ', ', array_map( static fn( mixed $val) => !is_string( $val ) ? get_debug_type($val) : $val, $item['args'] ) )
            );
        }
        
        array_unshift( $trace, [
            0,
            sprintf('%s:(%s)', $e->getFile(), $e->getLine()),
            '',
        ]);
        
        $this->consoleStyle->table($header, $trace);
    }
    
}