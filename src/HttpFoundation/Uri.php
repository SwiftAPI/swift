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
use Psr\Http\Message\UriInterface;

/**
 * Class Uri
 * @package Swift\HttpFoundation
 */
class Uri implements UriInterface {

    use LowercaseTrait;

    private const SCHEMES = [ 'http' => 80, 'https' => 443 ];

    private const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

    private const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    /** @var string Uri scheme. */
    private string $scheme = '';

    /** @var string Uri user info. */
    private string $userInfo = '';

    /** @var string Uri host. */
    private string $host = '';

    /** @var int|null Uri port. */
    private ?int $port;

    /** @var string Uri path. */
    private string $path = '';

    /** @var string Uri query string. */
    private string $query = '';

    /** @var string Uri fragment. */
    private string $fragment = '';

    public function __construct( string $uri = '' ) {
        if ( '' !== $uri ) {
            if ( false === $parts = \parse_url( $uri ) ) {
                throw new \InvalidArgumentException( "Unable to parse URI: $uri" );
            }

            // Apply parse_url parts to a URI.
            $this->scheme   = isset( $parts['scheme'] ) ? self::lowercase( $parts['scheme'] ) : '';
            $this->userInfo = $parts['user'] ?? '';
            $this->host     = isset( $parts['host'] ) ? self::lowercase( $parts['host'] ) : '';
            $this->port     = isset( $parts['port'] ) ? $this->filterPort( $parts['port'] ) : null;
            $this->path     = isset( $parts['path'] ) ? $this->filterPath( $parts['path'] ) : '';
            $this->query    = isset( $parts['query'] ) ? $this->filterQueryAndFragment( $parts['query'] ) : '';
            $this->fragment = isset( $parts['fragment'] ) ? $this->filterQueryAndFragment( $parts['fragment'] ) : '';
            if ( isset( $parts['pass'] ) ) {
                $this->userInfo .= ':' . $parts['pass'];
            }
        }
    }

    private function filterPort( $port ): ?int {
        if ( null === $port ) {
            return null;
        }

        $port = (int) $port;
        if ( 0 > $port || 0xffff < $port ) {
            throw new \InvalidArgumentException( \sprintf( 'Invalid port: %d. Must be between 0 and 65535', $port ) );
        }

        return self::isNonStandardPort( $this->scheme, $port ) ? $port : null;
    }

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @param string $scheme
     * @param int $port
     *
     * @return bool
     */
    private static function isNonStandardPort( string $scheme, int $port ): bool {
        return ! isset( self::SCHEMES[ $scheme ] ) || $port !== self::SCHEMES[ $scheme ];
    }

    private function filterPath( $path ): string {
        if ( ! \is_string( $path ) ) {
            throw new \InvalidArgumentException( 'Path must be a string' );
        }

        return \preg_replace_callback( '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/', [ __CLASS__, 'rawurlencodeMatchZero' ], $path );
    }

    private function filterQueryAndFragment( $str ): string {
        if ( ! \is_string( $str ) ) {
            throw new \InvalidArgumentException( 'Query and fragment must be a string' );
        }

        return \preg_replace_callback( '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/', [ __CLASS__, 'rawurlencodeMatchZero' ], $str );
    }

    #[Pure] private static function rawurlencodeMatchZero( array $match ): string {
        return \rawurlencode( $match[0] );
    }

    public function __toString(): string {
        return self::createUriString( $this->scheme, $this->getAuthority(), $this->path, $this->query, $this->fragment );
    }

    /**
     * Create a URI string from its various parts.
     *
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param string $query
     * @param string $fragment
     *
     * @return string
     */
    private static function createUriString( string $scheme, string $authority, string $path, string $query, string $fragment ): string {
        $uri = '';
        if ( '' !== $scheme ) {
            $uri .= $scheme . ':';
        }

        if ( '' !== $authority ) {
            $uri .= '//' . $authority;
        }

        if ( '' !== $path ) {
            if ( '/' !== $path[0] ) {
                if ( '' !== $authority ) {
                    // If the path is rootless and an authority is present, the path MUST be prefixed by "/"
                    $path = '/' . $path;
                }
            } elseif ( isset( $path[1] ) && '/' === $path[1] ) {
                if ( '' === $authority ) {
                    // If the path is starting with more than one "/" and no authority is present, the
                    // starting slashes MUST be reduced to one.
                    $path = '/' . \ltrim( $path, '/' );
                }
            }

            $uri .= $path;
        }

        if ( '' !== $query ) {
            $uri .= '?' . $query;
        }

        if ( '' !== $fragment ) {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    public function getAuthority(): string {
        if ( '' === $this->host ) {
            return '';
        }

        $authority = $this->host;
        if ( '' !== $this->userInfo ) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ( null !== $this->port ) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getScheme(): string {
        return $this->scheme;
    }

    public function getUserInfo(): string {
        return $this->userInfo;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getPort(): ?int {
        return $this->port;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getQuery(): string {
        return $this->query;
    }

    public function getFragment(): string {
        return $this->fragment;
    }

    public function withScheme( $scheme ): self {
        if ( ! \is_string( $scheme ) ) {
            throw new \InvalidArgumentException( 'Scheme must be a string' );
        }

        if ( $this->scheme === $scheme = self::lowercase( $scheme ) ) {
            return $this;
        }

        $new         = clone $this;
        $new->scheme = $scheme;
        $new->port   = $new->filterPort( $new->port );

        return $new;
    }

    public function withUserInfo( $user, $password = null ): self {
        $info = $user;
        if ( null !== $password && '' !== $password ) {
            $info .= ':' . $password;
        }

        if ( $this->userInfo === $info ) {
            return $this;
        }

        $new           = clone $this;
        $new->userInfo = $info;

        return $new;
    }

    public function withHost( $host ): self {
        if ( ! \is_string( $host ) ) {
            throw new \InvalidArgumentException( 'Host must be a string' );
        }

        if ( $this->host === $host = self::lowercase( $host ) ) {
            return $this;
        }

        $new       = clone $this;
        $new->host = $host;

        return $new;
    }

    public function withPort( $port ): self {
        if ( $this->port === $port = $this->filterPort( $port ) ) {
            return $this;
        }

        $new       = clone $this;
        $new->port = $port;

        return $new;
    }

    public function withPath( $path ): self {
        if ( $this->path === $path = $this->filterPath( $path ) ) {
            return $this;
        }

        $new       = clone $this;
        $new->path = $path;

        return $new;
    }

    public function withQuery( $query ): self {
        if ( $this->query === $query = $this->filterQueryAndFragment( $query ) ) {
            return $this;
        }

        $new        = clone $this;
        $new->query = $query;

        return $new;
    }

    public function withFragment( $fragment ): self {
        if ( $this->fragment === $fragment = $this->filterQueryAndFragment( $fragment ) ) {
            return $this;
        }

        $new           = clone $this;
        $new->fragment = $fragment;

        return $new;
    }
}