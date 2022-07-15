<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\WebSocket\HttpFoundation;

use Psr\Http\Message\StreamInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\LowercaseTrait;
use Swift\HttpFoundation\Stream;

/**
 * Response represents a websocket message.
 */
#[DI( exclude: true, autowire: false )]
abstract class Message implements MessageInterface {
    
    use LowercaseTrait;
    
    protected string $content;
    protected string|null $charset;
    
    /** @var StreamInterface|null */
    private ?StreamInterface $stream;
    
    /**
     * @param string|null $content
     */
    public function __construct( ?string $content = '' ) {

        if ('' !== $content && null !== $content) {
            $this->stream = Stream::create($content);
        }
    }
    
    
    /**
     * Returns the Response as an HTTP string.
     *
     * The string representation of the Response is the same as the
     * one that will be sent to the client only if the prepare() method
     * has been called before.
     *
     * @return string The Response as an HTTP string
     *
     * @see prepare()
     */
    public function __toString(): string {
        return $this->getBody()->getContents();
    }

//    /**
//     * Prepares the Response before it is sent to the client.
//     *
//     * This method tweaks the Response to ensure that it is
//     * compliant with RFC 2616. Most of the changes are based on
//     * the Request that is "associated" with this Response.
//     *
//     * @param Request $request
//     *
//     * @return $this
//     */
//    public function prepare( Request $request ): static {
//        $new = clone $this;
//        $headers = $new->headers;
//
//        if ( $new->isInformational() || $new->isEmpty() ) {
//            $new = $new->withBody(null);
//            $headers->remove( 'Content-Type' );
//            $headers->remove( 'Content-Length' );
//            // prevent PHP from sending the Content-Type header based on default_mimetype
//            ini_set( 'default_mimetype', '' );
//        } else {
//            // Content-type based on the Request
//            if ( ! $headers->has( 'Content-Type' ) ) {
//                $format = $request->getRequestFormat( null );
//                if ( null !== $format && $mimeType = $request->getMimeType( $format ) ) {
//                    $headers->set( 'Content-Type', $mimeType );
//                }
//            }
//
//            // Fix Content-Type
//            $charset = $this->charset ?: 'UTF-8';
//            if ( ! $headers->has( 'Content-Type' ) ) {
//                $headers->set( 'Content-Type', 'text/html; charset=' . $charset );
//            } elseif ( 0 === stripos( $headers->get( 'Content-Type' ), 'text/' ) && false === stripos( $headers->get( 'Content-Type' ), 'charset' ) ) {
//                // add the charset
//                $headers->set( 'Content-Type', $headers->get( 'Content-Type' ) . '; charset=' . $charset );
//            }
//
//            // Fix Content-Length
//            if ( $headers->has( 'Transfer-Encoding' ) ) {
//                $headers->remove( 'Content-Length' );
//            }
//
//            if ( $request->isMethod( 'HEAD' ) ) {
//                // cf. RFC2616 14.13
//                $length = $headers->get( 'Content-Length' );
//                $new = $new->withBody( null );
//                if ( $length ) {
//                    $headers->set( 'Content-Length', $length );
//                }
//            }
//        }
//
//        // Fix protocol
//        if ( 'HTTP/1.0' !== $request->server->get( 'SERVER_PROTOCOL' ) ) {
//            $new->protocol = '1.1';
//        }
//
//        // Check if we need to send extra expire info headers
//        if ( '1.0' === $this->getProtocolVersion() && str_contains( $headers->get( 'Cache-Control' ), 'no-cache' ) ) {
//            $headers->set( 'pragma', 'no-cache' );
//            $headers->set( 'expires', - 1 );
//        }
//
//        $new = $new->withEnsureIEOverSSLCompatibility( $request );
//
//        if ( $request->isSecure() ) {
//            foreach ( $headers->getCookies() as $cookie ) {
//                $cookie->setSecureDefault( true );
//            }
//        }
//
//        return $new;
//    }
    
    /**
     * Sends HTTP headers and content.
     *
     * @param \Swift\WebSocket\WsConnection $conn
     *
     * @return static
     */
    public function send( \Swift\WebSocket\WsConnection $conn ): static {
        $new = $this->sendContent( $conn );

        if ( \function_exists( 'fastcgi_finish_request' ) ) {
            fastcgi_finish_request();
        } elseif ( ! \in_array( \PHP_SAPI, [ 'cli', 'phpdbg' ], true ) ) {
            $new::closeOutputBuffers( 0, true );
        }

        return $new;
    }

    /**
     * Sends content for the current websocket response.
     *
     * @return $this
     */
    public function sendContent( \Swift\WebSocket\WsConnection $conn ): static {
        if (!isset($this->stream)) {
            return $this;
        }
        $this->stream->rewind();
        
        $conn->send( $this->stream->getContents() );

        return $this;
    }

    /**
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     *
     * @final
     *
     * @param int $targetLevel
     * @param bool $flush
     */
    public static function closeOutputBuffers( int $targetLevel, bool $flush ): void {
        $status = ob_get_status( true );
        $level  = \count( $status );
        $flags  = \PHP_OUTPUT_HANDLER_REMOVABLE | ( $flush ? \PHP_OUTPUT_HANDLER_FLUSHABLE : \PHP_OUTPUT_HANDLER_CLEANABLE );

        while ( $level -- > $targetLevel && ( $s = $status[ $level ] ) && ( $s['del'] ?? ( ! isset( $s['flags'] ) || ( $s['flags'] & $flags ) === $flags ) ) ) {
            if ( $flush ) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    /**
     * Retrieves the response charset.
     *
     * @final
     */
    public function getCharset(): ?string {
        return $this->charset;
    }

    /**
     * Sets the response charset.
     *
     * @param string $charset
     *
     * @return $this
     *
     * @final
     */
    public function withCharset( string $charset ): static {
        $new = clone $this;
        $new->charset = $charset;

        return $new;
    }
    
    public function getBody(): StreamInterface {
        if ( !isset($this->stream) || (null === $this->stream) ) {
            $this->stream = Stream::create( '' );
        }
        
        return $this->stream;
    }
    
    public function withBody( StreamInterface $body ): self {
        if ( $body === $this->stream ) {
            return $this;
        }
        
        $new         = clone $this;
        $new->stream = $body;
        
        return $new;
    }

    
}
