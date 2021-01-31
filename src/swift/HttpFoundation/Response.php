<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

// Help opcache.preload discover always-needed symbols
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Swift\Kernel\Attributes\DI;

class_exists( ResponseHeaderBag::class );

/**
 * Response represents an HTTP response.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
#[DI( exclude: true, autowire: false )]
class Response implements ResponseInterface {

    use MessageTrait;

    public const HTTP_CONTINUE = 100;
    public const HTTP_SWITCHING_PROTOCOLS = 101;
    public const HTTP_PROCESSING = 102;            // RFC2518
    public const HTTP_EARLY_HINTS = 103;           // RFC8297
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_ACCEPTED = 202;
    public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    public const HTTP_NO_CONTENT = 204;
    public const HTTP_RESET_CONTENT = 205;
    public const HTTP_PARTIAL_CONTENT = 206;
    public const HTTP_MULTI_STATUS = 207;          // RFC4918
    public const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    public const HTTP_IM_USED = 226;               // RFC3229
    public const HTTP_MULTIPLE_CHOICES = 300;
    public const HTTP_MOVED_PERMANENTLY = 301;
    public const HTTP_FOUND = 302;
    public const HTTP_SEE_OTHER = 303;
    public const HTTP_NOT_MODIFIED = 304;
    public const HTTP_USE_PROXY = 305;
    public const HTTP_RESERVED = 306;
    public const HTTP_TEMPORARY_REDIRECT = 307;
    public const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_PAYMENT_REQUIRED = 402;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_NOT_ACCEPTABLE = 406;
    public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const HTTP_REQUEST_TIMEOUT = 408;
    public const HTTP_CONFLICT = 409;
    public const HTTP_GONE = 410;
    public const HTTP_LENGTH_REQUIRED = 411;
    public const HTTP_PRECONDITION_FAILED = 412;
    public const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    public const HTTP_REQUEST_URI_TOO_LONG = 414;
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    public const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public const HTTP_EXPECTATION_FAILED = 417;
    public const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    public const HTTP_MISDIRECTED_REQUEST = 421;                                         // RFC7540
    public const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    public const HTTP_LOCKED = 423;                                                      // RFC4918
    public const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    public const HTTP_TOO_EARLY = 425;                                                   // RFC-ietf-httpbis-replay-04
    public const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    public const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    public const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    public const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    public const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_NOT_IMPLEMENTED = 501;
    public const HTTP_BAD_GATEWAY = 502;
    public const HTTP_SERVICE_UNAVAILABLE = 503;
    public const HTTP_GATEWAY_TIMEOUT = 504;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    public const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    public const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    public const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    public const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    public const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
     */
    private const HTTP_RESPONSE_CACHE_CONTROL_DIRECTIVES = [
        'must_revalidate'  => false,
        'no_cache'         => false,
        'no_store'         => false,
        'no_transform'     => false,
        'public'           => false,
        'private'          => false,
        'proxy_revalidate' => false,
        'max_age'          => true,
        's_maxage'         => true,
        'immutable'        => false,
        'last_modified'    => true,
        'etag'             => true,
    ];
    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2016-03-01).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    public static array $reasonPhrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Too Early',                                                   // RFC-ietf-httpbis-replay-04
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',                                     // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];
    protected string $content;
    protected string $version = '1.1';
    protected int $statusCode;
    protected string|null $reasonPhrase;
    protected string|null $charset;

    /**
     * @param string|null $content
     * @param int $status
     * @param array $headers
     */
    public function __construct( ?string $content = '', int $status = 200, array $headers = [] ) {
        $this->headers = new ResponseHeaderBag( $headers );
        $this->statusCode = $status;
        $this->reasonPhrase = self::$reasonPhrases[$this->statusCode] ?? '';

        if ('' !== $content && null !== $content) {
            $this->stream = Stream::create($content);
        }
    }

    public function getReasonPhrase(): string {
        return $this->reasonPhrase;
    }

    public function withStatus( $code, $reasonPhrase = '' ): static {
        if ( ! \is_int( $code ) && ! \is_string( $code ) ) {
            throw new \InvalidArgumentException( 'Status code has to be an integer' );
        }

        $code = (int) $code;
        if ( $code < 100 || $code > 599 ) {
            throw new \InvalidArgumentException( \sprintf( 'Status code has to be an integer between 100 and 599. A status code of %d was given', $code ) );
        }

        $new             = clone $this;
        $new->statusCode = $code;
        if ( ( null === $reasonPhrase || '' === $reasonPhrase ) && isset( self::$reasonPhrases[ $new->statusCode ] ) ) {
            $reasonPhrase = self::$reasonPhrases[ $new->statusCode ];
        }
        $new->reasonPhrase = $reasonPhrase;

        return $new;
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
        return
            sprintf( 'HTTP/%s %s %s', $this->version, $this->statusCode, $this->reasonPhrase ) . "\r\n" .
            $this->headers . "\r\n" .
            $this->getBody()->getContents();
    }

    /**
     * Clones the current Response instance.
     */
    public function __clone(): void {
        $this->headers = clone $this->headers;
    }

    /**
     * Prepares the Response before it is sent to the client.
     *
     * This method tweaks the Response to ensure that it is
     * compliant with RFC 2616. Most of the changes are based on
     * the Request that is "associated" with this Response.
     *
     * @param Request $request
     *
     * @return $this
     */
    public function prepare( Request $request ): static {
        $new = clone $this;
        $headers = $new->headers;

        if ( $new->isInformational() || $new->isEmpty() ) {
            $new = $new->withBody(null);
            $headers->remove( 'Content-Type' );
            $headers->remove( 'Content-Length' );
            // prevent PHP from sending the Content-Type header based on default_mimetype
            ini_set( 'default_mimetype', '' );
        } else {
            // Content-type based on the Request
            if ( ! $headers->has( 'Content-Type' ) ) {
                $format = $request->getRequestFormat( null );
                if ( null !== $format && $mimeType = $request->getMimeType( $format ) ) {
                    $headers->set( 'Content-Type', $mimeType );
                }
            }

            // Fix Content-Type
            $charset = $this->charset ?: 'UTF-8';
            if ( ! $headers->has( 'Content-Type' ) ) {
                $headers->set( 'Content-Type', 'text/html; charset=' . $charset );
            } elseif ( 0 === stripos( $headers->get( 'Content-Type' ), 'text/' ) && false === stripos( $headers->get( 'Content-Type' ), 'charset' ) ) {
                // add the charset
                $headers->set( 'Content-Type', $headers->get( 'Content-Type' ) . '; charset=' . $charset );
            }

            // Fix Content-Length
            if ( $headers->has( 'Transfer-Encoding' ) ) {
                $headers->remove( 'Content-Length' );
            }

            if ( $request->isMethod( 'HEAD' ) ) {
                // cf. RFC2616 14.13
                $length = $headers->get( 'Content-Length' );
                $new = $new->withBody( null );
                if ( $length ) {
                    $headers->set( 'Content-Length', $length );
                }
            }
        }

        // Fix protocol
        if ( 'HTTP/1.0' !== $request->server->get( 'SERVER_PROTOCOL' ) ) {
            $new->protocol = '1.1';
        }

        // Check if we need to send extra expire info headers
        if ( '1.0' === $this->getProtocolVersion() && str_contains( $headers->get( 'Cache-Control' ), 'no-cache' ) ) {
            $headers->set( 'pragma', 'no-cache' );
            $headers->set( 'expires', - 1 );
        }

        $new = $new->withEnsureIEOverSSLCompatibility( $request );

        if ( $request->isSecure() ) {
            foreach ( $headers->getCookies() as $cookie ) {
                $cookie->setSecureDefault( true );
            }
        }

        return $new;
    }

    /**
     * Is response informative?
     *
     * @final
     */
    public function isInformational(): bool {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * Is the response empty?
     *
     * @final
     */
    #[Pure] public function isEmpty(): bool {
        return \in_array( $this->statusCode, [ 204, 304 ] );
    }

    /**
     * Gets the HTTP protocol version.
     *
     * @final
     */
    public function getProtocolVersion(): string {
        return $this->version;
    }

    /**
     * Checks if we need to remove Cache-Control for SSL encrypted downloads when using IE < 9.
     *
     * @see http://support.microsoft.com/kb/323308
     *
     * @final
     *
     * @param Request $request
     *
     * @return static
     */
    protected function withEnsureIEOverSSLCompatibility( Request $request ): static {
        $new = clone $this;
        if ( false !== stripos( $this->headers->get( 'Content-Disposition' ), 'attachment' ) &&
             1 == preg_match( '/MSIE (.*?);/i', $request->server->get( 'HTTP_USER_AGENT' ), $match ) &&
             true === $request->isSecure() && (int) preg_replace( '/(MSIE )(.*?);/', '$2', $match[0] ) < 9
        ) {
            $new->headers->remove( 'Cache-Control' );
        }

        return $new;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return static
     */
    public function send(): static {
        $new = $this->sendHeaders();
        $new = $new->sendContent();

        if ( \function_exists( 'fastcgi_finish_request' ) ) {
            fastcgi_finish_request();
        } elseif ( ! \in_array( \PHP_SAPI, [ 'cli', 'phpdbg' ], true ) ) {
            $new::closeOutputBuffers( 0, true );
        }

        return $new;
    }

    /**
     * Sends HTTP headers.
     *
     * @return $this
     */
    public function sendHeaders(): static {
        $new = clone $this;
        // headers have already been sent by the developer
        if ( headers_sent() ) {
            return $new;
        }

        // headers
        foreach ( $new->headers->allPreserveCaseWithoutCookies() as $name => $values ) {
            $replace = 0 === strcasecmp( $name, 'Content-Type' );
            foreach ( $values as $value ) {
                header( $name . ': ' . $value, $replace, $new->statusCode );
            }
        }

        // cookies
        foreach ( $new->headers->getCookies() as $cookie ) {
            header( 'Set-Cookie: ' . $cookie, false, $new->statusCode );
        }

        // status
        header( sprintf( 'HTTP/%s %s %s', $new->version, $new->statusCode, $new->reasonPhrase ), true, $new->statusCode );

        return $new;
    }

    /**
     * Sends content for the current web response.
     *
     * @return $this
     */
    public function sendContent(): static {
        if (!isset($this->stream)) {
            return $this;
        }
        $this->stream->rewind();
        echo $this->stream->getContents();

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
     * Retrieves the status code for the current web response.
     *
     * @final
     */
    public function getStatusCode(): int {
        return $this->statusCode;
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

    /**
     * Returns true if the response may safely be kept in a shared (surrogate) cache.
     *
     * Responses marked "private" with an explicit Cache-Control directive are
     * considered uncacheable.
     *
     * Responses with neither a freshness lifetime (Expires, max-age) nor cache
     * validator (Last-Modified, ETag) are considered uncacheable because there is
     * no way to tell when or how to remove them from the cache.
     *
     * Note that RFC 7231 and RFC 7234 possibly allow for a more permissive implementation,
     * for example "status codes that are defined as cacheable by default [...]
     * can be reused by a cache with heuristic expiration unless otherwise indicated"
     * (https://tools.ietf.org/html/rfc7231#section-6.1)
     *
     * @final
     */
    public function isCacheable(): bool {
        if ( ! \in_array( $this->statusCode, [ 200, 203, 300, 301, 302, 404, 410 ] ) ) {
            return false;
        }

        if ( $this->headers->hasCacheControlDirective( 'no-store' ) || $this->headers->getCacheControlDirective( 'private' ) ) {
            return false;
        }

        return $this->isValidateable() || $this->isFresh();
    }

    /**
     * Returns true if the response includes headers that can be used to validate
     * the response with the origin server using a conditional GET request.
     *
     * @final
     */
    public function isValidateable(): bool {
        return $this->headers->has( 'Last-Modified' ) || $this->headers->has( 'ETag' );
    }

    /**
     * Returns true if the response is "fresh".
     *
     * Fresh responses may be served from cache without any interaction with the
     * origin. A response is considered fresh when it includes a Cache-Control/max-age
     * indicator or Expires header and the calculated age is less than the freshness lifetime.
     *
     * @final
     */
    public function isFresh(): bool {
        return $this->getTtl() > 0;
    }

    /**
     * Returns the response's time-to-live in seconds.
     *
     * It returns null when no freshness information is present in the response.
     *
     * When the responses TTL is <= 0, the response may not be served from cache without first
     * revalidating with the origin.
     *
     * @final
     */
    public function getTtl(): ?int {
        $maxAge = $this->getMaxAge();

        return null !== $maxAge ? $maxAge - $this->getAge() : null;
    }

    /**
     * Returns the number of seconds after the time specified in the response's Date
     * header when the response should no longer be considered fresh.
     *
     * First, it checks for a s-maxage directive, then a max-age directive, and then it falls
     * back on an expires header. It returns null when no maximum age can be established.
     *
     * @final
     */
    public function getMaxAge(): ?int {
        if ( $this->headers->hasCacheControlDirective( 's-maxage' ) ) {
            return (int) $this->headers->getCacheControlDirective( 's-maxage' );
        }

        if ( $this->headers->hasCacheControlDirective( 'max-age' ) ) {
            return (int) $this->headers->getCacheControlDirective( 'max-age' );
        }

        if ( null !== $this->getExpires() ) {
            return (int) $this->getExpires()->format( 'U' ) - (int) $this->getDate()->format( 'U' );
        }

        return null;
    }

    /**
     * Returns the value of the Expires header as a DateTime instance.
     *
     * @final
     */
    public function getExpires(): ?DateTimeInterface {
        try {
            return $this->headers->getDate( 'Expires' );
        } catch ( RuntimeException $e ) {
            // according to RFC 2616 invalid date formats (e.g. "0" and "-1") must be treated as in the past
            return DateTime::createFromFormat( 'U', time() - 172800 );
        }
    }

    /**
     * Returns the Date header as a DateTime instance.
     *
     * @throws RuntimeException When the header is not parseable
     *
     * @final
     */
    public function getDate(): ?DateTimeInterface {
        return $this->headers->getDate( 'Date' );
    }

    /**
     * Returns the age of the response in seconds.
     *
     * @final
     */
    public function getAge(): int {
        if ( null !== $age = $this->headers->get( 'Age' ) ) {
            return (int) $age;
        }

        return max( time() - (int) $this->getDate()->format( 'U' ), 0 );
    }

    /**
     * Marks the response as "immutable".
     *
     * @param bool $immutable
     *
     * @return $this
     *
     * @final
     */
    public function setImmutable( bool $immutable = true ): object {
        if ( $immutable ) {
            $this->headers->addCacheControlDirective( 'immutable' );
        } else {
            $this->headers->removeCacheControlDirective( 'immutable' );
        }

        return $this;
    }

    /**
     * Returns true if the response is marked as "immutable".
     *
     * @final
     */
    public function isImmutable(): bool {
        return $this->headers->hasCacheControlDirective( 'immutable' );
    }

    /**
     * Returns true if the response must be revalidated by shared caches once it has become stale.
     *
     * This method indicates that the response must not be served stale by a
     * cache in any circumstance without first revalidating with the origin.
     * When present, the TTL of the response should not be overridden to be
     * greater than the value provided by the origin.
     *
     * @final
     */
    public function mustRevalidate(): bool {
        return $this->headers->hasCacheControlDirective( 'must-revalidate' ) || $this->headers->hasCacheControlDirective( 'proxy-revalidate' );
    }

    /**
     * Sets the Date header.
     *
     * @param DateTimeInterface $date
     *
     * @return $this
     *
     * @final
     */
    public function withDate( DateTimeInterface $date ): static {
        if ( $date instanceof DateTime ) {
            $date = DateTimeImmutable::createFromMutable( $date );
        }

        $new = clone $this;

        $formatted = $date->setTimezone( new DateTimeZone( 'UTC' ) );
        $new->headers->set( 'Date', $formatted->format( 'D, d M Y H:i:s' ) . ' GMT' );

        return $new;
    }

    /**
     * Marks the response stale by setting the Age header to be equal to the maximum age of the response.
     *
     * @return $this
     */
    public function expire(): static {
        if ( $this->isFresh() ) {
            $this->headers->set( 'Age', $this->getMaxAge() );
            $this->headers->remove( 'Expires' );
        }

        return $this;
    }

    /**
     * Sets the Expires HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     *
     * @param DateTimeInterface|null $date
     *
     * @return $this
     *
     * @final
     */
    public function withExpires( ?DateTimeInterface $date = null ): static {
        $new = clone $this;
        if ( null === $date ) {
            $new->headers->remove( 'Expires' );

            return $new;
        }

        if ( $date instanceof DateTime ) {
            $date = DateTimeImmutable::createFromMutable( $date );
        }

        $formatted = $date->setTimezone( new DateTimeZone( 'UTC' ) );
        $new->headers->set( 'Expires', $formatted->format( 'D, d M Y H:i:s' ) . ' GMT' );

        return $new;
    }

    /**
     * Sets the response's time-to-live for shared caches in seconds.
     *
     * This method adjusts the Cache-Control/s-maxage directive.
     *
     * @param int $seconds
     *
     * @return static
     *
     * @final
     */
    public function withTtl( int $seconds ): static {
        $new = clone $this;
        $new = $new->withSharedMaxAge( $new->getAge() + $seconds );

        return $new;
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh by shared caches.
     *
     * This methods sets the Cache-Control s-maxage directive.
     *
     * @param int $value
     *
     * @return static
     *
     * @final
     */
    public function withSharedMaxAge( int $value ): static {
        $new = clone $this;
        $new->setPublic();
        $new->headers->addCacheControlDirective( 's-maxage', $value );

        return $new;
    }

    /**
     * Marks the response as "public".
     *
     * It makes the response eligible for serving other clients.
     *
     * @return static
     *
     * @final
     */
    public function withPublic(): static {
        $new = clone $this;
        $new->headers->addCacheControlDirective( 'public' );
        $new->headers->removeCacheControlDirective( 'private' );

        return $new;
    }

    /**
     * Sets the response's time-to-live for private/client caches in seconds.
     *
     * This method adjusts the Cache-Control/max-age directive.
     *
     * @param int $seconds
     *
     * @return static
     *
     * @final
     */
    public function setClientTtl( int $seconds ): static {
        return $this->withMaxAge( $this->getAge() + $seconds );
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh.
     *
     * This methods sets the Cache-Control max-age directive.
     *
     * @param int $value
     *
     * @return static
     *
     * @final
     */
    public function withMaxAge( int $value ): static {
        $new = clone $this;
        $new->headers->addCacheControlDirective( 'max-age', $value );

        return $new;
    }

    /**
     * Returns the Last-Modified HTTP header as a DateTime instance.
     *
     * @throws RuntimeException When the HTTP header is not parseable
     *
     * @final
     */
    public function getLastModified(): ?DateTimeInterface {
        return $this->headers->getDate( 'Last-Modified' );
    }

    /**
     * Sets the response's cache headers (validation and/or expiration).
     *
     * Available options are: must_revalidate, no_cache, no_store, no_transform, public, private, proxy_revalidate, max_age, s_maxage, immutable, last_modified and etag.
     *
     * @param array $options
     *
     * @return static
     *
     * @final
     */
    public function setCache( array $options ): static {
        if ( $diff = array_diff( array_keys( $options ), array_keys( self::HTTP_RESPONSE_CACHE_CONTROL_DIRECTIVES ) ) ) {
            throw new InvalidArgumentException( sprintf( 'Response does not support the following options: "%s".', implode( '", "', $diff ) ) );
        }

        $new = clone $this;

        if ( isset( $options['etag'] ) ) {
            $new = $new->withEtag( $options['etag'] );
        }

        if ( isset( $options['last_modified'] ) ) {
           $new = $new->withLastModified( $options['last_modified'] );
        }

        if ( isset( $options['max_age'] ) ) {
            $new = $new->withMaxAge( $options['max_age'] );
        }

        if ( isset( $options['s_maxage'] ) ) {
            $new = $new->withSharedMaxAge( $options['s_maxage'] );
        }

        foreach ( self::HTTP_RESPONSE_CACHE_CONTROL_DIRECTIVES as $directive => $hasValue ) {
            if ( ! $hasValue && isset( $options[ $directive ] ) ) {
                if ( $options[ $directive ] ) {
                    $new->headers->addCacheControlDirective( str_replace( '_', '-', $directive ) );
                } else {
                    $new->headers->removeCacheControlDirective( str_replace( '_', '-', $directive ) );
                }
            }
        }

        if ( isset( $options['public'] ) ) {
            if ( $options['public'] ) {
                $new = $new->withPublic();
            } else {
                $new = $new->withPrivate();
            }
        }

        if ( isset( $options['private'] ) ) {
            if ( $options['private'] ) {
                $new = $new->withPrivate();
            } else {
                $new = $new->withPublic();
            }
        }

        return $new;
    }

    /**
     * Sets the ETag value.
     *
     * @param string|null $etag The ETag unique identifier or null to remove the header
     * @param bool $weak Whether you want a weak ETag or not
     *
     * @return static
     *
     * @final
     */
    public function withEtag( string $etag = null, bool $weak = false ): static {
        $new = clone $this;
        if ( null === $etag ) {
            $new->headers->remove( 'Etag' );
        } else {
            if ( ! str_starts_with( $etag, '"' ) ) {
                $etag = '"' . $etag . '"';
            }

            $new->headers->set( 'ETag', ( true === $weak ? 'W/' : '' ) . $etag );
        }

        return $new;
    }

    /**
     * Sets the Last-Modified HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     *
     * @param DateTimeInterface|null $date
     *
     * @return static
     *
     * @final
     */
    public function withLastModified( DateTimeInterface $date = null ): static {
        $new = clone $this;
        if ( null === $date ) {
            $new->headers->remove( 'Last-Modified' );

            return $new;
        }

        if ( $date instanceof DateTime ) {
            $date = DateTimeImmutable::createFromMutable( $date );
        }

        $formatted = $date->setTimezone( new DateTimeZone( 'UTC' ) );
        $new->headers->set( 'Last-Modified', $formatted->format( 'D, d M Y H:i:s' ) . ' GMT' );

        return $new;
    }

    /**
     * Marks the response as "private".
     *
     * It makes the response ineligible for serving other clients.
     *
     * @return static
     *
     * @final
     */
    public function withPrivate(): static {
        $new = clone $this;
        $new->headers->removeCacheControlDirective( 'public' );
        $new->headers->addCacheControlDirective( 'private' );

        return $new;
    }

    /**
     * Returns true if the response includes a Vary header.
     *
     * @final
     */
    public function hasVary(): bool {
        return null !== $this->headers->get( 'Vary' );
    }

    /**
     * Returns an array of header names given in the Vary header.
     *
     * @final
     */
    public function getVary(): array {
        if ( ! $vary = $this->headers->all( 'Vary' ) ) {
            return [];
        }

        $ret = [];
        foreach ( $vary as $item ) {
            $ret = array_merge( $ret, preg_split( '/[\s,]+/', $item ) );
        }

        return $ret;
    }

    /**
     * Determines if the Response validators (ETag, Last-Modified) match
     * a conditional value specified in the Request.
     *
     * If the Response is not modified, it sets the status code to 304 and
     * removes the actual content by calling the setNotModified() method.
     *
     * @param Request $request
     *
     * @return bool true if the Response validators match the Request, false otherwise
     *
     * @final
     */
    public function isNotModified( Request $request ): bool {
        if ( ! $request->isMethodCacheable() ) {
            return false;
        }

        $notModified   = false;
        $lastModified  = $this->headers->get( 'Last-Modified' );
        $modifiedSince = $request->headers->get( 'If-Modified-Since' );

        if ( $etags = $request->getETags() ) {
            $notModified = \in_array( $this->getEtag(), $etags, true ) || \in_array( '*', $etags, true );
        }

        if ( $modifiedSince && $lastModified ) {
            $notModified = strtotime( $modifiedSince ) >= strtotime( $lastModified ) && ( ! $etags || $notModified );
        }

        if ( $notModified ) {
            $this->setNotModified();
        }

        return $notModified;
    }

    /**
     * Returns the literal value of the ETag HTTP header.
     *
     * @final
     */
    public function getEtag(): ?string {
        return $this->headers->get( 'ETag' );
    }

    /**
     * Modifies the response so that it conforms to the rules defined for a 304 status code.
     *
     * This sets the status, removes the body, and discards any headers
     * that MUST NOT be included in 304 responses.
     *
     * @return static
     *
     * @see https://tools.ietf.org/html/rfc2616#section-10.3.5
     *
     * @final
     */
    public function setNotModified(): static {
        $new = $this->withStatus( 304 )->withBody(null);

        // remove headers that MUST NOT be included with 304 Not Modified responses
        foreach ( [ 'Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified' ] as $header ) {
            $new->headers->remove( $header );
        }

        return $new;
    }

    /**
     * Is response invalid?
     *
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @final
     */
    public function isInvalid(): bool {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Is response successful?
     *
     * @final
     */
    public function isSuccessful(): bool {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Is the response a redirect?
     *
     * @final
     */
    public function isRedirection(): bool {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Is there a client error?
     *
     * @final
     */
    public function isClientError(): bool {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Was there a server side error?
     *
     * @final
     */
    public function isServerError(): bool {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Is the response OK?
     *
     * @final
     */
    public function isOk(): bool {
        return 200 === $this->statusCode;
    }

    /**
     * Is the response forbidden?
     *
     * @final
     */
    public function isForbidden(): bool {
        return 403 === $this->statusCode;
    }

    /**
     * Is the response a not found error?
     *
     * @final
     */
    public function isNotFound(): bool {
        return 404 === $this->statusCode;
    }

    /**
     * Is the response a redirect of some form?
     *
     * @final
     *
     * @param string|null $location
     *
     * @return bool
     */
    public function isRedirect( string $location = null ): bool {
        return \in_array( $this->statusCode, [ 201, 301, 302, 303, 307, 308 ] ) && ( null === $location ?: $location == $this->headers->get( 'Location' ) );
    }

    /**
     * Marks a response as safe according to RFC8674.
     *
     * @see https://tools.ietf.org/html/rfc8674
     *
     * @param bool $safe
     *
     * @return static
     */
    public function withContentSafe( bool $safe = true ): static {
        $new = clone $this;
        if ( $safe ) {
            $new->headers->set( 'Preference-Applied', 'safe' );
        } elseif ( 'safe' === $new->headers->get( 'Preference-Applied' ) ) {
            $new->headers->remove( 'Preference-Applied' );
        }

        return $new->withVary( 'Prefer', false );
    }

    /**
     * Sets the Vary header.
     *
     * @param string|array $headers
     * @param bool $replace Whether to replace the actual value or not (true by default)
     *
     * @return static
     *
     * @final
     */
    public function withVary( array|string $headers, bool $replace = true ): static {
        $new = clone $this;
        $new->headers->set( 'Vary', $headers, $replace );

        return $new;
    }
}
