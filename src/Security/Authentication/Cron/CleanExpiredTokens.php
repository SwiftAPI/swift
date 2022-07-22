<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Cron;


use Swift\Console\Style\ConsoleStyle;
use Swift\Cron\Job;
use Swift\Dbal\Arguments\ArgumentComparison;
use Swift\Dbal\Arguments\Where;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Orm\EntityManagerInterface;
use Swift\Security\Authentication\Entity\AccessTokenEntity;

#[Autowire]
class CleanExpiredTokens implements \Swift\Cron\CronInterface {
    
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }
    
    /**
     * @inheritDoc
     */
    public function getIdentifier(): string {
        return 'security:clean-expired-tokens';
    }
    
    /**
     * @inheritDoc
     */
    public function getDescription(): string {
        return 'Clean up expired access tokens';
    }
    
    public function configure( Job $job ): Job {
        return $job->daily();
    }
    
    public function run( ?ConsoleStyle $consoleStyle ): void {
        $consoleStyle?->writeln('Cleaning up expired access tokens');
        
        $accessTokens = $this->entityManager->findMany(
            AccessTokenEntity::class,
            [],
            [
                new Where( 'expires', ArgumentComparison::LESS_THAN, new \DateTimeImmutable() ),
            ],
        );
        
        foreach ($accessTokens as $accessToken) {
            $this->entityManager->delete($accessToken);
        }
        
        $consoleStyle?->writeln('Cleaned up ' . $accessTokens->count()  . ' expired access tokens');
        
        $this->entityManager->run();
    }
    
}