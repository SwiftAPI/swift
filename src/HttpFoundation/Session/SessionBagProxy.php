<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session;


use Swift\DependencyInjection\Attributes\DI;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @internal
 */
#[DI( autowire: false )]
final class SessionBagProxy implements SessionBagInterface {

    private SessionBagInterface $bag;
    private $data;
    private $usageIndex;
    private $usageReporter;

    public function __construct( SessionBagInterface $bag, array &$data, ?int &$usageIndex, ?callable $usageReporter ) {
        $this->bag           = $bag;
        $this->data          = &$data;
        $this->usageIndex    = &$usageIndex;
        $this->usageReporter = $usageReporter;
    }

    public function getBag(): SessionBagInterface {
        ++ $this->usageIndex;
        if ( $this->usageReporter && 0 <= $this->usageIndex ) {
            ( $this->usageReporter )();
        }

        return $this->bag;
    }

    public function isEmpty(): bool {
        if ( ! isset( $this->data[ $this->bag->getStorageKey() ] ) ) {
            return true;
        }
        ++ $this->usageIndex;
        if ( $this->usageReporter && 0 <= $this->usageIndex ) {
            ( $this->usageReporter )();
        }

        return empty( $this->data[ $this->bag->getStorageKey() ] );
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string {
        return $this->bag->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize( array &$array ): void {
        ++ $this->usageIndex;
        if ( $this->usageReporter && 0 <= $this->usageIndex ) {
            ( $this->usageReporter )();
        }

        $this->data[ $this->bag->getStorageKey() ] = &$array;

        $this->bag->initialize( $array );
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageKey(): string {
        return $this->bag->getStorageKey();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): mixed {
        return $this->bag->clear();
    }
}
