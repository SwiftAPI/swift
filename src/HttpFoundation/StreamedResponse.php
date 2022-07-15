<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;


use Swift\DependencyInjection\Attributes\DI;/**
 * StreamedResponse represents a streamed HTTP response.
 *
 * A StreamedResponse uses a callback for its content.
 *
 * The callback should use the standard PHP functions like echo
 * to stream the response back to the client. The flush() function
 * can also be used if needed.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *@see flush()
 *
 */
#[DI( exclude: true, autowire: false )]
class StreamedResponse extends Response {

    protected $callback;
    protected bool $streamed;
    private bool $headersSent;

    public function __construct( callable $callback = null, int $status = 200, array $headers = [] ) {
        parent::__construct( null, $status, $headers );

        if ( null !== $callback ) {
            $this->setCallback( $callback );
        }
        $this->streamed    = false;
        $this->headersSent = false;
    }

    /**
     * Sets the PHP callback associated with this Response.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setCallback( callable $callback ): static {
        $this->callback = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * This method only sends the headers once.
     *
     * @return $this
     */
    public function sendHeaders(): static {
        if ( $this->headersSent ) {
            return $this;
        }

        $this->headersSent = true;

        return parent::sendHeaders();
    }

    /**
     * {@inheritdoc}
     *
     * This method only sends the content once.
     *
     * @return $this
     */
    public function sendContent(): static {
        if ( $this->streamed ) {
            return $this;
        }

        $this->streamed = true;

        if ( null === $this->callback ) {
            throw new \LogicException( 'The Response callback must not be null.' );
        }

        ( $this->callback )();

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @throws \LogicException when the content is not null
     *
     */
    public function setContent( ?string $content ): static {
        if ( null !== $content ) {
            throw new \LogicException( 'The content cannot be set on a StreamedResponse instance.' );
        }

        $this->streamed = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(): string|bool {
        return false;
    }
}
