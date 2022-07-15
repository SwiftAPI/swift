<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Runtime;


use JetBrains\PhpStorm\NoReturn;
use React\EventLoop\LoopInterface;
use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\Console\Style\ConsoleStyle;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Events\EventDispatcherInterface;
use Swift\HttpFoundation\Response;
use Swift\HttpFoundation\ServerRequest;
use Swift\Kernel\Event\KernelOnBeforeShutdown;
use Swift\Runtime\Cli\AbstractRuntimeCommand;

require_once INCLUDE_DIR . '/vendor/autoload_runtime.php';

#[Autowire]
class Kernel implements KernelInterface {
    
    private readonly LoopInterface $loop;
    
    /**
     * @var \Swift\Runtime\RuntimeKernelInterface[] $runtimes
     */
    private readonly array $runtimes;
    
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ConfigurationInterface $configuration,
    ) {
        $this->loop = \React\EventLoop\Loop::get();
    }
    
    public function run( ConsoleStyle $io, AbstractRuntimeCommand $command ): int {
        $io->writeln(
            '<fg=green>
   _______          _______ ______ _______   _____  _    _ _   _ _______ _____ __  __ ______
  / ____\ \        / /_   _|  ____|__   __| |  __ \| |  | | \ | |__   __|_   _|  \/  |  ____|
 | (___  \ \  /\  / /  | | | |__     | |    | |__) | |  | |  \| |  | |    | | | \  / | |__
  \___ \  \ \/  \/ /   | | |  __|    | |    |  _  /| |  | | . ` |  | |    | | | |\/| |  __|
  ____) |  \  /\  /   _| |_| |       | |    | | \ \| |__| | |\  |  | |   _| |_| |  | | |____
 |_____/    \/  \/   |_____|_|       |_|    |_|  \_\\____/|_| \_|  |_|  |_____|_|  |_|______|
        </>'
        );
        
        try {
            foreach ( $this->runtimes as $runtime ) {
                $runtime->run( $io, $command, $this->loop );
            }
            
            $this->loop->run();
        } catch ( \Throwable $e ) {
            $io->error( $e->getMessage() );
        }
        
        return 1;
    }
    
    
    public function isDebug(): bool {
        return Utils::isDebug( $this->configuration );
    }
    
    #[NoReturn]
    public function finalize( int $code = 0 ): void {
        $this->shutdown( $code );
    }
    
    /**
     * Shut down application after outputting response
     *
     */
    #[NoReturn]
    private function shutdown( int $code ): void {
        $this->eventDispatcher->dispatch( event: new KernelOnBeforeShutdown( request: new ServerRequest(), response: new Response() ) );
        
        exit( $code );
    }
    
    public function getLoop(): LoopInterface {
        return $this->loop;
    }
    
    #[Autowire]
    public function setRuntimeKernels( #[Autowire( tag: RuntimeDiTags::RUNTIME )] ?iterable $kernels ): void {
        if ( ! $kernels ) {
            $this->runtimes = [];
            
            return;
        }
        
        $this->runtimes = iterator_to_array( $kernels, false );
    }
    
}