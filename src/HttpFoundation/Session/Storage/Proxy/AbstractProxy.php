<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Storage\Proxy;


use Swift\DependencyInjection\Attributes\DI;

/**
 * @author Drak <drak@zikula.org>
 */
#[DI( autowire: false )]
abstract class AbstractProxy {

    /**
     * Flag if handler wraps an internal PHP session handler (using \SessionHandler).
     *
     * @var bool
     */
    protected bool $wrapper = false;

    /**
     * @var string
     */
    protected string $saveHandlerName;

    /**
     * Gets the session.save_handler name.
     *
     * @return string|null
     */
    public function getSaveHandlerName(): ?string {
        return $this->saveHandlerName;
    }

    /**
     * Is this proxy handler and instance of \SessionHandlerInterface.
     *
     * @return bool
     */
    public function isSessionHandlerInterface(): bool {
        return $this instanceof \SessionHandlerInterface;
    }

    /**
     * Returns true if this handler wraps an internal PHP session save handler using \SessionHandler.
     *
     * @return bool
     */
    public function isWrapper(): bool {
        return $this->wrapper;
    }

    /**
     * Gets the session ID.
     *
     * @return string
     */
    public function getId(): string {
        return session_id();
    }

    /**
     * Sets the session ID.
     *
     * @param string $id
     */
    public function setId( string $id ): void {
        if ( $this->isActive() ) {
            throw new \LogicException( 'Cannot change the ID of an active session.' );
        }

        session_id( $id );
    }

    /**
     * Has a session started?
     *
     * @return bool
     */
    public function isActive(): bool {
        return \PHP_SESSION_ACTIVE === session_status();
    }

    /**
     * Gets the session name.
     *
     * @return string
     */
    public function getName(): string {
        return session_name();
    }

    /**
     * Sets the session name.
     *
     * @param string $name
     */
    public function setName( string $name ): void {
        if ( $this->isActive() ) {
            throw new \LogicException( 'Cannot change the name of an active session.' );
        }

        session_name( $name );
    }
}
