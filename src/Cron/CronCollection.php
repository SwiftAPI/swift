<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Cron;

use Swift\DependencyInjection\Attributes\Autowire;

#[Autowire]
class CronCollection {
    
    /** @var \Swift\Cron\CronInterface[] */
    private readonly array $crons;
    
    /**
     * @return \Swift\Cron\CronInterface[]
     */
    public function getCrons(): array {
        return $this->crons;
    }
    
    #[Autowire]
    public function setCrons( #[Autowire( tag: CronDiTags::CRON )] ?iterable $crons ): void {
        if ( ! $crons ) {
            $this->crons = [];
            
            return;
        }
        
        /** @var \Swift\Cron\CronInterface[] $cronItems */
        
        $cronItems = [];
        foreach (iterator_to_array( $crons, false ) as /** @var \Swift\Cron\CronInterface $cron */ $cron) {
            if (array_key_exists($cron->getIdentifier(), $cronItems)) {
                throw new \InvalidArgumentException( sprintf('Duplicate cron identifier "%s" detected in "%s"', $cron->getIdentifier(), $cron::class) );
            }
            $cronItems[$cron->getIdentifier()] = $cron;
        }
        
        $this->crons = $cronItems;
    }
    
    public function getCronByIdentifier( string $identifier ): ?CronInterface {
        return $this->crons[$identifier] ?? null;
    }
    
}