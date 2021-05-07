<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use JetBrains\PhpStorm\Pure;
use Swift\Kernel\Attributes\DI;

/**
 * Request stack that controls the lifecycle of requests.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class RequestStack {

    /**
     * @var Request[]
     */
    private array $requests = [];

    /**
     * Pushes a Request on the stack.
     *
     * This method should generally not be called directly as the stack
     * management should be taken care of by the application itself.
     *
     * @param Request $request
     */
    public function push( Request $request ): void {
        $this->requests[] = $request;
    }

    /**
     * Pops the current request from the stack.
     *
     * This operation lets the current request go out of scope.
     *
     * This method should generally not be called directly as the stack
     * management should be taken care of by the application itself.
     *
     * @return Request|null
     */
    public function pop(): ?Request {
        if ( ! $this->requests ) {
            return null;
        }

        return array_pop( $this->requests );
    }

    /**
     * @return Request|null
     */
    public function getCurrentRequest(): ?Request {
        return end( $this->requests ) ?: null;
    }

    /**
     * Gets the master Request.
     *
     * Be warned that making your code aware of the master request
     * might make it un-compatible with other features of your framework
     * like ESI support.
     *
     * @return Request|null
     */
    public function getMasterRequest(): ?Request {
        if ( ! $this->requests ) {
            return null;
        }

        return $this->requests[0];
    }

    /**
     * Returns the parent request of the current.
     *
     * Be warned that making your code aware of the parent request
     * might make it un-compatible with other features of your framework
     * like ESI support.
     *
     * If current Request is the master request, it returns null.
     *
     * @return Request|null
     */
    #[Pure] public function getParentRequest(): ?Request {
        $pos = \count( $this->requests ) - 2;

        return $this->requests[ $pos ] ?? null;
    }
}
