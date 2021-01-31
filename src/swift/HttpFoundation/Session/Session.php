<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session;

use Swift\HttpFoundation\Session\Attribute\AttributeBag;
use Swift\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Swift\HttpFoundation\Session\Flash\FlashBag;
use Swift\HttpFoundation\Session\Flash\FlashBagInterface;
use Swift\HttpFoundation\Session\Storage\MetadataBag;
use Swift\HttpFoundation\Session\Storage\NativeSessionStorage;
use Swift\HttpFoundation\Session\Storage\SessionStorageInterface;

// Help opcache.preload discover always-needed symbols
class_exists( AttributeBag::class );
class_exists( FlashBag::class );
class_exists( SessionBagProxy::class );

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Drak <drak@zikula.org>
 */
class Session implements SessionInterface, \IteratorAggregate, \Countable {
    protected $storage;

    private string $flashName;
    private string $attributeName;
    private array $data = [];
    private int $usageIndex = 0;
    private $usageReporter;

    public function __construct( SessionStorageInterface $storage = null, AttributeBagInterface $attributes = null, FlashBagInterface $flashes = null, callable $usageReporter = null ) {
        $this->storage       = $storage ?: new NativeSessionStorage();
        $this->usageReporter = $usageReporter;

        $attributes          = $attributes ?: new AttributeBag();
        $this->attributeName = $attributes->getName();
        $this->registerBag( $attributes );

        $flashes         = $flashes ?: new FlashBag();
        $this->flashName = $flashes->getName();
        $this->registerBag( $flashes );
    }

    /**
     * {@inheritdoc}
     */
    public function registerBag( SessionBagInterface $bag ): void {
        $this->storage->registerBag( new SessionBagProxy( $bag, $this->data, $this->usageIndex, $this->usageReporter ) );
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool {
        return $this->storage->start();
    }

    /**
     * {@inheritdoc}
     */
    public function has( string $name ): bool {
        return $this->getAttributeBag()->has( $name );
    }

    /**
     * Gets the attributebag interface.
     *
     * Note that this method was added to help with IDE autocompletion.
     */
    private function getAttributeBag(): SessionBagInterface {
        return $this->getBag( $this->attributeName );
    }

    /**
     * {@inheritdoc}
     */
    public function getBag( string $name ) {
        $bag = $this->storage->getBag( $name );

        return method_exists( $bag, 'getBag' ) ? $bag->getBag() : $bag;
    }

    /**
     * {@inheritdoc}
     */
    public function get( string $name, $default = null ): mixed {
        return $this->getAttributeBag()->get( $name, $default );
    }

    /**
     * {@inheritdoc}
     */
    public function set( string $name, mixed $value ): void {
        $this->getAttributeBag()->set( $name, $value );
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array {
        return $this->getAttributeBag()->all();
    }

    /**
     * {@inheritdoc}
     */
    public function replace( array $attributes ): void {
        $this->getAttributeBag()->replace( $attributes );
    }

    /**
     * {@inheritdoc}
     */
    public function remove( string $name ): mixed {
        return $this->getAttributeBag()->remove( $name );
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void {
        $this->getAttributeBag()->clear();
    }

    /**
     * Returns an iterator for attributes.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator( $this->getAttributeBag()->all() );
    }

    /**
     * Returns the number of attributes.
     *
     * @return int
     */
    public function count(): int {
        return \count( $this->getAttributeBag()->all() );
    }

    public function &getUsageIndex(): int {
        return $this->usageIndex;
    }

    /**
     * @internal
     */
    public function isEmpty(): bool {
        if ( $this->isStarted() ) {
            ++ $this->usageIndex;
            if ( $this->usageReporter && 0 <= $this->usageIndex ) {
                ( $this->usageReporter )();
            }
        }
        foreach ( $this->data as &$data ) {
            if ( ! empty( $data ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted(): bool {
        return $this->storage->isStarted();
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate( int $lifetime = null ): bool {
        $this->storage->clear();

        return $this->migrate( true, $lifetime );
    }

    /**
     * {@inheritdoc}
     */
    public function migrate( bool $destroy = false, int $lifetime = null ): bool {
        return $this->storage->regenerate( $destroy, $lifetime );
    }

    /**
     * {@inheritdoc}
     */
    public function save(): void {
        $this->storage->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string {
        return $this->storage->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function setId( string $id ): void {
        if ( $this->storage->getId() !== $id ) {
            $this->storage->setId( $id );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string {
        return $this->storage->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setName( string $name ): void {
        $this->storage->setName( $name );
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataBag(): MetadataBag {
        ++ $this->usageIndex;
        if ( $this->usageReporter && 0 <= $this->usageIndex ) {
            ( $this->usageReporter )();
        }

        return $this->storage->getMetadataBag();
    }

    /**
     * Gets the flashbag interface.
     *
     * @return FlashBagInterface
     */
    public function getFlashBag(): FlashBagInterface {
        return $this->getBag( $this->flashName );
    }
}
