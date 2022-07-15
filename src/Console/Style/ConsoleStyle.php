<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Console\Style;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleStyle extends \Symfony\Component\Console\Style\SymfonyStyle {
    
    public function __construct(
        protected readonly InputInterface  $input,
        protected readonly OutputInterface $output,
    ) {
        parent::__construct( $this->input, $this->output );
    }
    
    /**
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    public function getInput(): InputInterface {
        return $this->input;
    }
    
    /**
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput(): OutputInterface {
        return $this->output;
    }
    
    /**
     * @inheritDoc
     */
    public function isQuiet(): bool {
        return parent::isQuiet();
    }
    
    /**
     * @inheritDoc
     */
    public function isVerbose(): bool {
        return parent::isVerbose();
    }
    
    /**
     * @inheritDoc
     */
    public function isVeryVerbose(): bool {
        return parent::isVeryVerbose();
    }
    
    /**
     * @inheritDoc
     */
    public function isDebug(): bool {
        return parent::isDebug();
    }
    
}