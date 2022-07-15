<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\CoRoutines;

use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class CoRoutineCollection {
    
    /** @var \Swift\CoRoutines\CoRoutineInterface[] */
    private readonly array $coroutines;
    
    /**
     * @return \Swift\CoRoutines\CoRoutineInterface[]
     */
    public function getCoroutines(): array {
        return $this->coroutines;
    }
    
    #[Autowire]
    public function setCoroutines( #[Autowire( tag: CoRoutineDiTags::COROUTINE )] ?iterable $coroutines ): void {
        if ( ! $coroutines ) {
            $this->coroutines = [];
            
            return;
        }
        
        /** @var \Swift\Coroutines\CoroutineInterface[] $coroutineItems */
        
        $coroutineItems = [];
        foreach (iterator_to_array( $coroutines, false ) as /** @var \Swift\CoRoutines\CoRoutineInterface $coroutine */ $coroutine) {
            if (array_key_exists($coroutine->getIdentifier(), $coroutineItems)) {
                throw new \InvalidArgumentException( sprintf('Duplicate coroutine identifier "%s" detected in "%s"', $coroutine->getIdentifier(), $coroutine::class) );
            }
            $coroutineItems[$coroutine->getIdentifier()] = $coroutine;
        }
        
        $this->coroutines = $coroutineItems;
    }
    
    public function getCoroutineByIdentifier( string $identifier ): ?CoRoutineInterface {
        return $this->coroutines[$identifier] ?? null;
    }
    
}