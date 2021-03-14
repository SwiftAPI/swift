<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use Psr\Http\Message\StreamInterface;

/**
 * Trait MessageTrait
 * @package Swift\HttpFoundation
 */
trait MessageTrait {

    use LowercaseTrait;

    /**
     * Headers (taken from the $_SERVER).
     *
     * @var HeaderBag|ResponseHeaderBag
     */
    public HeaderBag|ResponseHeaderBag $headers;

    /** @var array Map of lowercase header name => original name at registration */
    private array $headerNames = [];

    /** @var string */
    private string $protocol = '1.1';

    /** @var StreamInterface|null */
    private ?StreamInterface $stream;

    public function getProtocolVersion(): string {
        return $this->protocol;
    }

    public function withProtocolVersion( $version ): self {
        if ( $this->protocol === $version ) {
            return $this;
        }

        $new           = clone $this;
        $new->protocol = $version;

        return $new;
    }

    public function getHeaders(): HeaderBag {
        return $this->headers;
    }

    public function hasHeader( $header ): bool {
        return $this->headers->has( self::lowercase( $header ) );
    }

    public function getHeaderLine( $header ): string {
        return \implode( ', ', $this->getHeader( $header ) );
    }

    public function getHeader( $header ): array {
        return array( $this->headers->get( self::lowercase( $header ) ) );
    }

    public function withHeader( $header, $value ): self {
        $value      = $this->validateAndTrimHeader( $header, $value );
        $normalized = self::lowercase( $header );

        $new = clone $this;
        if ( $new->headers->has( $normalized ) ) {
            $new->headers->remove( $normalized );
        }
        $new->headers->add( [ $normalized => $value ] );

        return $new;
    }

    /**
     * Make sure the header complies with RFC 7230.
     *
     * Header names must be a non-empty string consisting of token characters.
     *
     * Header values must be strings consisting of visible characters with all optional
     * leading and trailing whitespace stripped. This method will always strip such
     * optional whitespace. Note that the method does not allow folding whitespace within
     * the values as this was deprecated for almost all instances by the RFC.
     *
     * header-field = field-name ":" OWS field-value OWS
     * field-name   = 1*( "!" / "#" / "$" / "%" / "&" / "'" / "*" / "+" / "-" / "." / "^"
     *              / "_" / "`" / "|" / "~" / %x30-39 / ( %x41-5A / %x61-7A ) )
     * OWS          = *( SP / HTAB )
     * field-value  = *( ( %x21-7E / %x80-FF ) [ 1*( SP / HTAB ) ( %x21-7E / %x80-FF ) ] )
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     *
     * @param $header
     * @param $values
     *
     * @return array
     */
    private function validateAndTrimHeader( $header, $values ): array {
        if ( ! \is_string( $header ) || 1 !== \preg_match( "@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $header ) ) {
            throw new \InvalidArgumentException( 'Header name must be an RFC 7230 compatible string.' );
        }

        if ( ! \is_array( $values ) ) {
            // This is simple, just one value.
            if ( ( ! \is_numeric( $values ) && ! \is_string( $values ) ) || 1 !== \preg_match( "@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $values ) ) {
                throw new \InvalidArgumentException( 'Header values must be RFC 7230 compatible strings.' );
            }

            return [ \trim( (string) $values, " \t" ) ];
        }

        if ( empty( $values ) ) {
            throw new \InvalidArgumentException( 'Header values must be a string or an array of strings, empty array given.' );
        }

        // Assert Non empty array
        $returnValues = [];
        foreach ( $values as $v ) {
            if ( ( ! \is_numeric( $v ) && ! \is_string( $v ) ) || 1 !== \preg_match( "@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $v ) ) {
                throw new \InvalidArgumentException( 'Header values must be RFC 7230 compatible strings.' );
            }

            $returnValues[] = \trim( (string) $v, " \t" );
        }

        return $returnValues;
    }

    public function withAddedHeader( $header, $value ): self {
        if ( ! \is_string( $header ) || '' === $header ) {
            throw new \InvalidArgumentException( 'Header name must be an RFC 7230 compatible string.' );
        }

        $new = clone $this;
        $new->setHeaders( [ $header => $value ] );

        return $new;
    }

    private function setHeaders( array $headers ): void {
        foreach ( $headers as $header => $value ) {
            if ( \is_int( $header ) ) {
                // If a header name was set to a numeric string, PHP will cast the key to an int.
                // We must cast it back to a string in order to comply with validation.
                $header = (string) $header;
            }
            $value      = $this->validateAndTrimHeader( $header, $value );
            $normalized = self::lowercase( $header );
            $this->headers->set( $normalized, $value );
        }
    }

    public function withoutHeader( $header ): self {
        $normalized = self::lowercase( $header );
        if ( $this->headers->has( $normalized ) ) {
            return $this;
        }

        $new = clone $this;
        $new->headers->remove( $normalized );

        return $new;
    }

    public function getBody(): StreamInterface {
        if ( !isset($this->stream) || (null === $this->stream) ) {
            var_dump('here');
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