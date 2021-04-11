<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use GraphQL\Language\Parser;
use GraphQL\Server\Helper;
use GraphQL\Utils\Utils;
use Swift\Configuration\Configuration;
use Swift\HttpFoundation\Exception\ConflictingHeadersException;
use Swift\HttpFoundation\Exception\JsonException;
use Swift\HttpFoundation\Exception\SuspiciousOperationException;
use Swift\HttpFoundation\Session\SessionInterface;
use Swift\Kernel\ServiceLocator;
use function in_array;


/**
 * Request represents an HTTP request.
 *
 * The methods dealing with URL accept / return a raw path (% encoded):
 *   * getBasePath
 *   * getBaseUrl
 *   * getPathInfo
 *   * getRequestUri
 *   * getUri
 *   * getUriForPath
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Request implements RequestInterface {

    use MessageTrait;
    use RequestTrait;

    public const HEADER_FORWARDED = 0b000001; // When using RFC 7239
    public const HEADER_X_FORWARDED_FOR = 0b000010;
    public const HEADER_X_FORWARDED_HOST = 0b000100;
    public const HEADER_X_FORWARDED_PROTO = 0b001000;
    public const HEADER_X_FORWARDED_PORT = 0b010000;
    public const HEADER_X_FORWARDED_PREFIX = 0b100000;

    /** @deprecated since Symfony 5.2, use either "HEADER_X_FORWARDED_FOR | HEADER_X_FORWARDED_HOST | HEADER_X_FORWARDED_PORT | HEADER_X_FORWARDED_PROTO" or "HEADER_X_FORWARDED_AWS_ELB" or "HEADER_X_FORWARDED_TRAEFIK" constants instead. */
    public const HEADER_X_FORWARDED_ALL = 0b1011110; // All "X-Forwarded-*" headers sent by "usual" reverse proxy
    public const HEADER_X_FORWARDED_AWS_ELB = 0b0011010; // AWS ELB doesn't send X-Forwarded-Host
    public const HEADER_X_FORWARDED_TRAEFIK = 0b0111110; // All "X-Forwarded-*" headers sent by Traefik reverse proxy

    public const METHOD_HEAD = 'HEAD';
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_PURGE = 'PURGE';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_TRACE = 'TRACE';
    public const METHOD_CONNECT = 'CONNECT';

    /**
     * @var string[]
     */
    protected static array $trustedProxies = [];

    /**
     * @var string[]
     */
    protected static array $trustedHostPatterns = [];

    /**
     * @var string[]
     */
    protected static array $trustedHosts = [];

    protected static bool $httpMethodParameterOverride = false;
    protected static array $formats;
    protected static $requestFactory;
    private static int $trustedHeaderSet = - 1;
    private static array $forwardedParams = [
        self::HEADER_X_FORWARDED_FOR   => 'for',
        self::HEADER_X_FORWARDED_HOST  => 'host',
        self::HEADER_X_FORWARDED_PROTO => 'proto',
        self::HEADER_X_FORWARDED_PORT  => 'host',
    ];
    /**
     * Names for headers that can be trusted when
     * using trusted proxies.
     *
     * The FORWARDED header is the standard as of rfc7239.
     *
     * The other headers are non-standard, but widely used
     * by popular reverse proxies (like Apache mod_proxy or Amazon EC2).
     */
    private static array $trustedHeaders = [
        self::HEADER_FORWARDED          => 'FORWARDED',
        self::HEADER_X_FORWARDED_FOR    => 'X_FORWARDED_FOR',
        self::HEADER_X_FORWARDED_HOST   => 'X_FORWARDED_HOST',
        self::HEADER_X_FORWARDED_PROTO  => 'X_FORWARDED_PROTO',
        self::HEADER_X_FORWARDED_PORT   => 'X_FORWARDED_PORT',
        self::HEADER_X_FORWARDED_PREFIX => 'X_FORWARDED_PREFIX',
    ];
    /**
     * Custom parameters.
     *
     * @var ParameterBag
     */
    public ParameterBag $attributes;
    /**
     * Request body parameters ($_POST).
     *
     * @var InputBag|ParameterBag
     */
    public InputBag|ParameterBag $request;
    /**
     * Query string parameters ($_GET).
     *
     * @var InputBag
     */
    public InputBag $query;
    /**
     * Server and execution environment parameters ($_SERVER).
     *
     * @var ServerBag
     */
    public ServerBag $server;
    /**
     * Uploaded files ($_FILES).
     *
     * @var FileBag
     */
    public FileBag $files;
    /**
     * Cookies ($_COOKIE).
     *
     * @var InputBag
     */
    public InputBag $cookies;

    /**
     * @var string|resource|false|null
     */
    protected $content;
    protected array|null $languages;
    protected array|null $charsets;
    protected array|null $encodings;
    protected array|null $acceptableContentTypes;
    protected string|null $pathInfo;
    protected string|null $requestUri;
    protected string|null $baseUrl;
    protected string|null $basePath;
    protected string|null $format;
    /**
     * @var SessionInterface|callable
     */
    protected $session;
    protected string|null $locale;
    protected string $defaultLocale = 'en';
    private ?string $preferredFormat;
    private bool $isHostValid = true;
    private bool $isForwardedValid = true;
    private ?bool $isSafeContentPreferred;

    /**
     * @param array $query The GET parameters
     * @param array $request The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies The COOKIE parameters
     * @param array $files The FILES parameters
     * @param array $server The SERVER parameters
     * @param string|resource|null $content The raw body data
     */
    public function __construct( array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [] ) {
        if ( empty( $query ) && empty( $request ) && empty( $attributes ) && empty( $cookies ) && empty( $files ) && empty( $server ) ) {
            if (empty($_POST)) {
                $_POST = $this->decodeInput();
            }
            $this->initialize( $_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER );

            return;
        }

        $this->initialize( $query, $request, $attributes, $cookies, $files, $server );
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array $query The GET parameters
     * @param array $request The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies The COOKIE parameters
     * @param array $files The FILES parameters
     * @param array $server The SERVER parameters
     */
    public function initialize( array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [] ): void {
        $this->request    = new ParameterBag( $request );
        $this->query      = new InputBag( $query );
        $this->attributes = new ParameterBag( $attributes );
        $this->cookies    = new InputBag( $cookies );
        $this->files      = new FileBag( $files );
        $this->server     = new ServerBag( $server );
        $this->headers    = new HeaderBag( $this->server->getHeaders() );

        $this->stream                 = Stream::create($this->jsonEncode($request));
        $this->languages              = null;
        $this->charsets               = null;
        $this->encodings              = null;
        $this->acceptableContentTypes = null;
        $this->pathInfo               = null;
        $this->requestUri             = null;
        $this->baseUrl                = null;
        $this->basePath               = null;
        $this->method                 = (PHP_SAPI !== "cli") ? $this->getMethod() : 'GET';
        $this->format                 = null;
        $this->uri                    = (PHP_SAPI !== "cli") ? new Uri($this->getUriString()) : null;

        if ((PHP_SAPI !== "cli") && ($this->getUri()->getPath() === '/graphql')) {
            $this->stream = Stream::create($this->jsonEncode($this->getGraphQlStructure()));
        }
    }

    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return static
     */
    public static function createFromGlobals(): static {
        $request = self::createRequestFromFactory( $_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER );

        $contentType = $request->headers->get( 'CONTENT_TYPE' ) ?? '';

        if ( $_POST ) {
            $request->request = new InputBag( $_POST );
        } elseif ( str_starts_with( $contentType, 'application/x-www-form-urlencoded' )
                   && in_array( strtoupper( $request->server->get( 'REQUEST_METHOD', 'GET' ) ), [ 'PUT', 'DELETE', 'PATCH' ] )
        ) {
            parse_str( $request->getContent(), $data );
            $request->request = new InputBag( $data );
        }

        return $request;
    }

    private static function createRequestFromFactory( array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null ): self {
        if ( self::$requestFactory ) {
            $request = ( self::$requestFactory )( $query, $request, $attributes, $cookies, $files, $server, $content );

            if ( ! $request instanceof self ) {
                throw new \LogicException( 'The Request factory must return an instance of Swift\HttpFoundation\Request.' );
            }

            return $request;
        }

        return new static( $query, $request, $attributes, $cookies, $files, $server, $content );
    }

    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return ParameterBag The request body content or a resource to read the body stream
     * @throws \JsonException
     */
    public function getContent( bool $asResource = false ): ParameterBag {
        $content = $this->getBody();

        if ( true === $asResource ) {
            $content->rewind();

            return $content->getResource();
        }

        $content->rewind();
        $body = $content->getContents();

        try {
            $body = is_string($body) ? json_decode( $body, true, 512, JSON_THROW_ON_ERROR ) : $body;
        } catch (\JsonException) {
            $body = is_string($body) ? array($body) : $body;
        }

        return new ParameterBag($body);
    }

    /**
     * @inheritDoc
     */
    public function isPreflight(): bool {
        return (($this->getMethod() === 'OPTIONS') || ($this->getMethod() === 'HEAD'));
    }


    /**
     * Creates a Request based on a given URI and configuration.
     *
     * The information contained in the URI always take precedence
     * over the other information (server and parameters).
     *
     * @param string $uri The URI
     * @param string $method The HTTP method
     * @param array $parameters The query (GET) or request (POST) parameters
     * @param array $cookies The request cookies ($_COOKIE)
     * @param array $files The request files ($_FILES)
     * @param array $server The server parameters ($_SERVER)
     * @param string|resource|null $content The raw body data
     *
     * @return static
     */
    public static function create( string $uri, string $method = 'GET', array $parameters = [], array $cookies = [], array $files = [], array $server = [], $content = null ): static {
        $server = array_replace( [
            'SERVER_NAME'          => 'localhost',
            'SERVER_PORT'          => 80,
            'HTTP_HOST'            => 'localhost',
            'HTTP_USER_AGENT'      => 'Swift',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR'          => '127.0.0.1',
            'SCRIPT_NAME'          => '',
            'SCRIPT_FILENAME'      => '',
            'SERVER_PROTOCOL'      => 'HTTP/1.1',
            'REQUEST_TIME'         => time(),
        ], $server );

        $server['PATH_INFO']      = '';
        $server['REQUEST_METHOD'] = strtoupper( $method );

        $components = parse_url( $uri );
        if ( isset( $components['host'] ) ) {
            $server['SERVER_NAME'] = $components['host'];
            $server['HTTP_HOST']   = $components['host'];
        }

        if ( isset( $components['scheme'] ) ) {
            if ( 'https' === $components['scheme'] ) {
                $server['HTTPS']       = 'on';
                $server['SERVER_PORT'] = 443;
            } else {
                unset( $server['HTTPS'] );
                $server['SERVER_PORT'] = 80;
            }
        }

        if ( isset( $components['port'] ) ) {
            $server['SERVER_PORT'] = $components['port'];
            $server['HTTP_HOST']   .= ':' . $components['port'];
        }

        if ( isset( $components['user'] ) ) {
            $server['PHP_AUTH_USER'] = $components['user'];
        }

        if ( isset( $components['pass'] ) ) {
            $server['PHP_AUTH_PW'] = $components['pass'];
        }

        if ( ! isset( $components['path'] ) ) {
            $components['path'] = '/';
        }

        switch ( strtoupper( $method ) ) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                if ( ! isset( $server['CONTENT_TYPE'] ) ) {
                    $server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
                }
            // no break
            case 'PATCH':
                $request = $parameters;
                $query   = [];
                break;
            default:
                $request = [];
                $query   = $parameters;
                break;
        }

        $queryString = '';
        if ( isset( $components['query'] ) ) {
            parse_str( html_entity_decode( $components['query'] ), $qs );

            if ( $query ) {
                $query       = array_replace( $qs, $query );
                $queryString = http_build_query( $query, '', '&' );
            } else {
                $query       = $qs;
                $queryString = $components['query'];
            }
        } elseif ( $query ) {
            $queryString = http_build_query( $query, '', '&' );
        }

        $server['REQUEST_URI']  = $components['path'] . ( '' !== $queryString ? '?' . $queryString : '' );
        $server['QUERY_STRING'] = $queryString;

        return self::createRequestFromFactory( $query, $request, [], $cookies, $files, $server, $content );
    }

    /**
     * Sets a callable able to create a Request instance.
     *
     * This is mainly useful when you need to override the Request class
     * to keep BC with an existing system. It should not be used for any
     * other purpose.
     *
     * @param callable|null $callable
     */
    public static function setFactory( ?callable $callable ): void {
        self::$requestFactory = $callable;
    }

    /**
     * Gets the list of trusted proxies.
     *
     * @return array An array of trusted proxies
     */
    public static function getTrustedProxies(): array {
        return self::$trustedProxies;
    }

    /**
     * Sets a list of trusted proxies.
     *
     * You should only list the reverse proxies that you manage directly.
     *
     * @param array $proxies A list of trusted proxies, the string 'REMOTE_ADDR' will be replaced with $_SERVER['REMOTE_ADDR']
     * @param int $trustedHeaderSet A bit field of Request::HEADER_*, to set which headers to trust from your proxies
     */
    public static function setTrustedProxies( array $proxies, int $trustedHeaderSet ): void {
        if ( self::HEADER_X_FORWARDED_ALL === $trustedHeaderSet ) {
            trigger_deprecation( 'symfony/http-foundation', '5.2', 'The "HEADER_X_FORWARDED_ALL" constant is deprecated, use either "HEADER_X_FORWARDED_FOR | HEADER_X_FORWARDED_HOST | HEADER_X_FORWARDED_PORT | HEADER_X_FORWARDED_PROTO" or "HEADER_X_FORWARDED_AWS_ELB" or "HEADER_X_FORWARDED_TRAEFIK" constants instead.' );
        }
        self::$trustedProxies   = array_reduce( $proxies, static function ( $proxies, $proxy ) {
            if ( 'REMOTE_ADDR' !== $proxy ) {
                $proxies[] = $proxy;
            } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
                $proxies[] = $_SERVER['REMOTE_ADDR'];
            }

            return $proxies;
        }, [] );
        self::$trustedHeaderSet = $trustedHeaderSet;
    }

    /**
     * Gets the set of trusted headers from trusted proxies.
     *
     * @return int A bit field of Request::HEADER_* that defines which headers are trusted from your proxies
     */
    public static function getTrustedHeaderSet(): int {
        return self::$trustedHeaderSet;
    }

    /**
     * Gets the list of trusted host patterns.
     *
     * @return array An array of trusted host patterns
     */
    public static function getTrustedHosts(): array {
        return self::$trustedHostPatterns;
    }

    /**
     * Sets a list of trusted host patterns.
     *
     * You should only list the hosts you manage using regexs.
     *
     * @param array $hostPatterns A list of trusted host patterns
     */
    public static function setTrustedHosts( array $hostPatterns ): void {
        self::$trustedHostPatterns = array_map( static function ( $hostPattern ) {
            return sprintf( '{%s}i', $hostPattern );
        }, $hostPatterns );
        // we need to reset trusted hosts on trusted host patterns change
        self::$trustedHosts = [];
    }

    /**
     * Enables support for the _method request parameter to determine the intended HTTP method.
     *
     * Be warned that enabling this feature might lead to CSRF issues in your code.
     * Check that you are using CSRF tokens when required.
     * If the HTTP method parameter override is enabled, an html-form with method "POST" can be altered
     * and used to send a "PUT" or "DELETE" request via the _method request parameter.
     * If these methods are not protected against CSRF, this presents a possible vulnerability.
     *
     * The HTTP method can only be overridden when the real HTTP method is POST.
     */
    public static function enableHttpMethodParameterOverride(): void {
        self::$httpMethodParameterOverride = true;
    }

    /**
     * Checks whether support for the _method request parameter is enabled.
     *
     * @return bool True when the _method request parameter is enabled, false otherwise
     */
    public static function getHttpMethodParameterOverride(): bool {
        return self::$httpMethodParameterOverride;
    }

    /**
     * Gets the mime types associated with the format.
     *
     * @param string $format
     *
     * @return array The associated mime types
     */
    public static function getMimeTypes( string $format ): array {
        if ( null === static::$formats ) {
            static::initializeFormats();
        }

        return static::$formats[ $format ] ?? [];
    }

    /**
     * Clones a request and overrides some of its parameters.
     *
     * @param array|null $query The GET parameters
     * @param array|null $request The POST parameters
     * @param array|null $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array|null $cookies The COOKIE parameters
     * @param array|null $files The FILES parameters
     * @param array|null $server The SERVER parameters
     *
     * @return static
     */
    public function duplicate( array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null ): static {
        $dup = clone $this;
        if ( null !== $query ) {
            $dup->query = new InputBag( $query );
        }
        if ( null !== $request ) {
            $dup->request = new ParameterBag( $request );
        }
        if ( null !== $attributes ) {
            $dup->attributes = new ParameterBag( $attributes );
        }
        if ( null !== $cookies ) {
            $dup->cookies = new InputBag( $cookies );
        }
        if ( null !== $files ) {
            $dup->files = new FileBag( $files );
        }
        if ( null !== $server ) {
            $dup->server  = new ServerBag( $server );
            $dup->headers = new HeaderBag( $dup->server->getHeaders() );
        }
        $dup->languages              = null;
        $dup->charsets               = null;
        $dup->encodings              = null;
        $dup->acceptableContentTypes = null;
        $dup->pathInfo               = null;
        $dup->requestUri             = null;
        $dup->baseUrl                = null;
        $dup->basePath               = null;
        $dup->method                 = null;
        $dup->format                 = null;

        if ( ! $dup->get( '_format' ) && $this->get( '_format' ) ) {
            $dup->attributes->set( '_format', $this->get( '_format' ) );
        }

        if ( ! $dup->getRequestFormat( null ) ) {
            $dup->setRequestFormat( $this->getRequestFormat( null ) );
        }

        return $dup;
    }

    /**
     * Gets a "parameter" value from any bag.
     *
     * This method is mainly useful for libraries that want to provide some flexibility. If you don't need the
     * flexibility in controllers, it is better to explicitly get request parameters from the appropriate
     * public property instead (attributes, query, request).
     *
     * Order of precedence: PATH (routing placeholders or custom attributes), GET, BODY
     *
     * @param string $key
     * @param mixed $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get( string $key, $default = null ): mixed {
        if ( $this !== $result = $this->attributes->get( $key, $this ) ) {
            return $result;
        }

        if ( $this->query->has( $key ) ) {
            return $this->query->all()[ $key ];
        }

        if ( $this->request->has( $key ) ) {
            return $this->request->all()[ $key ];
        }

        return $default;
    }

    /**
     * Gets the request format.
     *
     * Here is the process to determine the format:
     *
     *  * format defined by the user (with setRequestFormat())
     *  * _format request attribute
     *  * $default
     *
     * @param string|null $default
     *
     * @return string|null The request format
     * @see getPreferredFormat
     */
    public function getRequestFormat( ?string $default = 'html' ): ?string {
        if ( null === $this->format ) {
            $this->format = $this->attributes->get( '_format' );
        }

        return $this->format ?? $default;
    }

    /**
     * Sets the request format.
     *
     * @param string|null $format
     */
    public function setRequestFormat( ?string $format ): void {
        $this->format = $format;
    }

    /**
     * Clones the current request.
     *
     * Note that the session is not cloned as duplicated requests
     * are most of the time sub-requests of the main one.
     */
    public function __clone() {
        $this->query      = clone $this->query;
        $this->request    = clone $this->request;
        $this->attributes = clone $this->attributes;
        $this->cookies    = clone $this->cookies;
        $this->files      = clone $this->files;
        $this->server     = clone $this->server;
        $this->headers    = clone $this->headers;
    }

    /**
     * Returns the request as a string.
     *
     * @return string The request
     */
    public function __toString(): string {
        $content = $this->getContent();

        $cookieHeader = '';
        $cookies      = [];

        foreach ( $this->cookies as $k => $v ) {
            $cookies[] = $k . '=' . $v;
        }

        if ( ! empty( $cookies ) ) {
            $cookieHeader = 'Cookie: ' . implode( '; ', $cookies ) . "\r\n";
        }

        return
            sprintf( '%s %s %s', $this->getMethod(), $this->getRequestUri(), $this->server->get( 'SERVER_PROTOCOL' ) ) . "\r\n" .
            $this->headers .
            $cookieHeader . "\r\n" .
            $content;
    }

    /**
     * Gets the request "intended" method.
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP method,
     * but only if enableHttpMethodParameterOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @return string The request method
     *
     * @see getRealMethod()
     */
    public function getMethod(): string {
        if ( null !== $this->method ) {
            return $this->method;
        }

        $this->method = strtoupper( $this->server->get( 'REQUEST_METHOD', 'GET' ) );

        if ( 'POST' !== $this->method ) {
            return $this->method;
        }

        $method = $this->headers->get( 'X-HTTP-METHOD-OVERRIDE' );

        if ( ! $method && self::$httpMethodParameterOverride ) {
            $method = $this->request->get( '_method', $this->query->get( '_method', 'POST' ) );
        }

        if ( ! \is_string( $method ) ) {
            return $this->method;
        }

        $method = strtoupper( $method );

        if ( in_array( $method, [ 'GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE' ], true ) ) {
            return $this->method = $method;
        }

        if ( ! preg_match( '/^[A-Z]++$/D', $method ) ) {
            throw new SuspiciousOperationException( sprintf( 'Invalid method override "%s".', $method ) );
        }

        return $this->method = $method;
    }

    /**
     * Sets the request method.
     *
     * @param string $method
     */
    public function setMethod( string $method ): void {
        $this->method = null;
        $this->server->set( 'REQUEST_METHOD', $method );
    }

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string|null The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri(): ?string {
        if ( null === $this->requestUri ) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    protected function prepareRequestUri() {
        $requestUri = '';

        if ( '1' == $this->server->get( 'IIS_WasUrlRewritten' ) && '' != $this->server->get( 'UNENCODED_URL' ) ) {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = $this->server->get( 'UNENCODED_URL' );
            $this->server->remove( 'UNENCODED_URL' );
            $this->server->remove( 'IIS_WasUrlRewritten' );
        } elseif ( $this->server->has( 'REQUEST_URI' ) ) {
            $requestUri = $this->server->get( 'REQUEST_URI' );

            if ( '' !== $requestUri && '/' === $requestUri[0] ) {
                // To only use path and query remove the fragment.
                if ( false !== $pos = strpos( $requestUri, '#' ) ) {
                    $requestUri = substr( $requestUri, 0, $pos );
                }
            } else {
                // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
                // only use URL path.
                $uriComponents = parse_url( $requestUri );

                if ( isset( $uriComponents['path'] ) ) {
                    $requestUri = $uriComponents['path'];
                }

                if ( isset( $uriComponents['query'] ) ) {
                    $requestUri .= '?' . $uriComponents['query'];
                }
            }
        } elseif ( $this->server->has( 'ORIG_PATH_INFO' ) ) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->get( 'ORIG_PATH_INFO' );
            if ( '' != $this->server->get( 'QUERY_STRING' ) ) {
                $requestUri .= '?' . $this->server->get( 'QUERY_STRING' );
            }
            $this->server->remove( 'ORIG_PATH_INFO' );
        }

        // Replace baseurl to make routing work
        if ($this->getHost() === 'localhost') {
            /** @var Configuration $config */
            $config = (new ServiceLocator())->get(Configuration::class);
            $requestUri = trim(str_replace($config->get('routing.baseurl', 'root'), '', trim($requestUri, '/')), '/');
        }

        // normalize the request URI to ease creating sub-requests from this request
        $this->server->set( 'REQUEST_URI', $requestUri );

        return $requestUri;
    }

    /**
     * Overrides the PHP global variables according to this request instance.
     *
     * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE.
     * $_FILES is never overridden, see rfc1867
     */
    public function overrideGlobals(): void {
        $this->server->set( 'QUERY_STRING', static::normalizeQueryString( http_build_query( $this->query->all(), '', '&' ) ) );

        $_GET    = $this->query->all();
        $_POST   = $this->request->all();
        $_SERVER = $this->server->all();
        $_COOKIE = $this->cookies->all();

        foreach ( $this->headers->all() as $key => $value ) {
            $key = strtoupper( str_replace( '-', '_', $key ) );
            if ( in_array( $key, [ 'CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5' ], true ) ) {
                $_SERVER[ $key ] = implode( ', ', $value );
            } else {
                $_SERVER[ 'HTTP_' . $key ] = implode( ', ', $value );
            }
        }

        $request = [ 'g' => $_GET, 'p' => $_POST, 'c' => $_COOKIE ];

        $requestOrder = ini_get( 'request_order' ) ?: ini_get( 'variables_order' );
        $requestOrder = preg_replace( '#[^cgp]#', '', strtolower( $requestOrder ) ) ?: 'gp';

        $_REQUEST = [ [] ];

        foreach ( str_split( $requestOrder ) as $order ) {
            $_REQUEST[] = $request[ $order ];
        }

        $_REQUEST = array_merge( ...$_REQUEST );
    }

    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     *
     * @param string|null $qs
     *
     * @return string A normalized query string for the Request
     */
    public static function normalizeQueryString( ?string $qs ): string {
        if ( '' === ( $qs ?? '' ) ) {
            return '';
        }

        $parsed = HeaderUtils::parseQuery( $qs );
        ksort( $parsed );

        return http_build_query( $parsed, '', '&', \PHP_QUERY_RFC3986 );
    }

    /**
     * Whether the request contains a Session which was started in one of the
     * previous requests.
     *
     * @return bool
     */
    public function hasPreviousSession(): bool {
        // the check for $this->session avoids malicious users trying to fake a session cookie with proper name
        return $this->hasSession() && $this->cookies->has( $this->getSession()->getName() );
    }

    /**
     * Whether the request contains a Session object.
     *
     * This method does not give any information about the state of the session object,
     * like whether the session is started or not. It is just a way to check if this Request
     * is associated with a Session instance.
     *
     * @return bool true when the Request contains a Session object, false otherwise
     */
    public function hasSession(): bool {
        return null !== $this->session;
    }

    /**
     * Gets the Session.
     *
     * @return callable|SessionInterface The session
     */
    public function getSession(): callable|SessionInterface {
        $session = $this->session;
        if ( ! $session instanceof SessionInterface && null !== $session ) {
            $this->setSession( $session = $session() );
        }

        if ( null === $session ) {
            throw new \BadMethodCallException( 'Session has not been set.' );
        }

        return $session;
    }

    public function setSession( SessionInterface $session ): static {
        $this->session = $session;

        return $this;
    }

    /**
     * @param callable $factory
     *
     * @return Request
     * @internal
     */
    public function setSessionFactory( callable $factory ): static {
        $this->session = $factory;

        return $this;
    }

    /**
     * Returns the client IP address.
     *
     * This method can read the client IP address from the "X-Forwarded-For" header
     * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
     * header value is a comma+space separated list of IP addresses, the left-most
     * being the original client, and each successive proxy that passed the request
     * adding the IP address where it received the request from.
     *
     * If your reverse proxy uses a different header name than "X-Forwarded-For",
     * ("Client-Ip" for instance), configure it via the $trustedHeaderSet
     * argument of the Request::setTrustedProxies() method instead.
     *
     * @return string|null The client IP address
     *
     * @see getClientIps()
     * @see https://wikipedia.org/wiki/X-Forwarded-For
     */
    public function getClientIp(): ?string {
        $ipAddresses = $this->getClientIps();

        return $ipAddresses[0];
    }

    /**
     * Returns the client IP addresses.
     *
     * In the returned array the most trusted IP address is first, and the
     * least trusted one last. The "real" client IP address is the last one,
     * but this is also the least trusted one. Trusted proxies are stripped.
     *
     * Use this method carefully; you should use getClientIp() instead.
     *
     * @return array The client IP addresses
     *
     * @see getClientIp()
     */
    public function getClientIps(): array {
        $ip = $this->server->get( 'REMOTE_ADDR' );

        if ( ! $this->isFromTrustedProxy() ) {
            return [ $ip ];
        }

        return $this->getTrustedValues( self::HEADER_X_FORWARDED_FOR, $ip ) ?: [ $ip ];
    }

    /**
     * Indicates whether this request originated from a trusted proxy.
     *
     * This can be useful to determine whether or not to trust the
     * contents of a proxy-specific header.
     *
     * @return bool true if the request came from a trusted proxy, false otherwise
     */
    public function isFromTrustedProxy(): bool {
        return self::$trustedProxies && IpUtils::checkIp( $this->server->get( 'REMOTE_ADDR' ), self::$trustedProxies );
    }

    private function getTrustedValues( int $type, string $ip = null ): array {
        $clientValues    = [];
        $forwardedValues = [];

        if ( ( self::$trustedHeaderSet & $type ) && $this->headers->has( self::$trustedHeaders[ $type ] ) ) {
            foreach ( explode( ',', $this->headers->get( self::$trustedHeaders[ $type ] ) ) as $v ) {
                $clientValues[] = ( self::HEADER_X_FORWARDED_PORT === $type ? '0.0.0.0:' : '' ) . trim( $v );
            }
        }

        if ( ( self::$trustedHeaderSet & self::HEADER_FORWARDED ) && ( isset( self::$forwardedParams[ $type ] ) ) && $this->headers->has( self::$trustedHeaders[ self::HEADER_FORWARDED ] ) ) {
            $forwarded       = $this->headers->get( self::$trustedHeaders[ self::HEADER_FORWARDED ] );
            $parts           = HeaderUtils::split( $forwarded, ',;=' );
            $forwardedValues = [];
            $param           = self::$forwardedParams[ $type ];
            foreach ( $parts as $subParts ) {
                if ( null === $v = HeaderUtils::combine( $subParts )[ $param ] ?? null ) {
                    continue;
                }
                if ( self::HEADER_X_FORWARDED_PORT === $type ) {
                    if ( ']' === substr( $v, - 1 ) || false === $v = strrchr( $v, ':' ) ) {
                        $v = $this->isSecure() ? ':443' : ':80';
                    }
                    $v = '0.0.0.0' . $v;
                }
                $forwardedValues[] = $v;
            }
        }

        if ( null !== $ip ) {
            $clientValues    = $this->normalizeAndFilterClientIps( $clientValues, $ip );
            $forwardedValues = $this->normalizeAndFilterClientIps( $forwardedValues, $ip );
        }

        if ( $forwardedValues === $clientValues || ! $clientValues ) {
            return $forwardedValues;
        }

        if ( ! $forwardedValues ) {
            return $clientValues;
        }

        if ( ! $this->isForwardedValid ) {
            return null !== $ip ? [ '0.0.0.0', $ip ] : [];
        }
        $this->isForwardedValid = false;

        throw new ConflictingHeadersException( sprintf( 'The request has both a trusted "%s" header and a trusted "%s" header, conflicting with each other. You should either configure your proxy to remove one of them, or configure your project to distrust the offending one.', self::$trustedHeaders[ self::HEADER_FORWARDED ], self::$trustedHeaders[ $type ] ) );
    }

    /**
     * Checks whether the request is secure or not.
     *
     * This method can read the client protocol from the "X-Forwarded-Proto" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
     *
     * @return bool
     */
    public function isSecure(): bool {
        if ( $this->isFromTrustedProxy() && $proto = $this->getTrustedValues( self::HEADER_X_FORWARDED_PROTO ) ) {
            return in_array( strtolower( $proto[0] ), [ 'https', 'on', 'ssl', '1' ], true );
        }

        $https = $this->server->get( 'HTTPS' );

        return ! empty( $https ) && 'off' !== strtolower( $https );
    }

    private function normalizeAndFilterClientIps( array $clientIps, string $ip ): array {
        if ( ! $clientIps ) {
            return [];
        }
        $clientIps[]    = $ip; // Complete the IP chain with the IP the request actually came from
        $firstTrustedIp = null;

        foreach ( $clientIps as $key => $clientIp ) {
            if ( strpos( $clientIp, '.' ) ) {
                // Strip :port from IPv4 addresses. This is allowed in Forwarded
                // and may occur in X-Forwarded-For.
                $i = strpos( $clientIp, ':' );
                if ( $i ) {
                    $clientIps[ $key ] = $clientIp = substr( $clientIp, 0, $i );
                }
            } elseif ( 0 === strpos( $clientIp, '[' ) ) {
                // Strip brackets and :port from IPv6 addresses.
                $i                 = strpos( $clientIp, ']', 1 );
                $clientIps[ $key ] = $clientIp = substr( $clientIp, 1, $i - 1 );
            }

            if ( ! filter_var( $clientIp, \FILTER_VALIDATE_IP ) ) {
                unset( $clientIps[ $key ] );

                continue;
            }

            if ( IpUtils::checkIp( $clientIp, self::$trustedProxies ) ) {
                unset( $clientIps[ $key ] );

                // Fallback to this when the client IP falls into the range of trusted proxies
                if ( null === $firstTrustedIp ) {
                    $firstTrustedIp = $clientIp;
                }
            }
        }

        // Now the IP chain contains only untrusted proxies and the client IP
        return $clientIps ? array_reverse( $clientIps ) : [ $firstTrustedIp ];
    }

    /**
     * Returns current script name.
     *
     * @return string
     */
    public function getScriptName(): string {
        return $this->server->get( 'SCRIPT_NAME', $this->server->get( 'ORIG_SCRIPT_NAME', '' ) );
    }

    /**
     * Returns the root path from which this request is executed.
     *
     * Suppose that an index.php file instantiates this request object:
     *
     *  * http://localhost/index.php         returns an empty string
     *  * http://localhost/index.php/page    returns an empty string
     *  * http://localhost/web/index.php     returns '/web'
     *  * http://localhost/we%20b/index.php  returns '/we%20b'
     *
     * @return string|null The raw path (i.e. not urldecoded)
     */
    public function getBasePath(): ?string {
        if ( null === $this->basePath ) {
            $this->basePath = $this->prepareBasePath();
        }

        return $this->basePath;
    }

    /**
     * Prepares the base path.
     *
     * @return string base path
     */
    protected function prepareBasePath(): string {
        $baseUrl = $this->getBaseUrl();
        if ( empty( $baseUrl ) ) {
            return '';
        }

        $filename = basename( $this->server->get( 'SCRIPT_FILENAME' ) );
        if ( basename( $baseUrl ) === $filename ) {
            $basePath = \dirname( $baseUrl );
        } else {
            $basePath = $baseUrl;
        }

        if ( '\\' === \DIRECTORY_SEPARATOR ) {
            $basePath = str_replace( '\\', '/', $basePath );
        }

        return rtrim( $basePath, '/' );
    }

    /**
     * Returns the root URL from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getBasePath(), except that it also includes the
     * script filename (e.g. index.php) if one exists.
     *
     * @return string The raw URL (i.e. not urldecoded)
     */
    public function getBaseUrl(): string {
        $trustedPrefix = '';

        // the proxy prefix must be prepended to any prefix being needed at the webserver level
        if ( $this->isFromTrustedProxy() && $trustedPrefixValues = $this->getTrustedValues( self::HEADER_X_FORWARDED_PREFIX ) ) {
            $trustedPrefix = rtrim( $trustedPrefixValues[0], '/' );
        }

        return $trustedPrefix . $this->getBaseUrlReal();
    }

    /**
     * Returns the real base URL received by the webserver from which this request is executed.
     * The URL does not include trusted reverse proxy prefix.
     *
     * @return string|null The raw URL (i.e. not urldecoded)
     */
    private function getBaseUrlReal(): ?string {
        if ( null === $this->baseUrl ) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    /**
     * Prepares the base URL.
     *
     * @return string|null
     */
    protected function prepareBaseUrl(): ?string {
        $filename = basename( $this->server->get( 'SCRIPT_FILENAME' ) );

        if ( basename( $this->server->get( 'SCRIPT_NAME' ) ) === $filename ) {
            $baseUrl = $this->server->get( 'SCRIPT_NAME' );
        } elseif ( basename( $this->server->get( 'PHP_SELF' ) ) === $filename ) {
            $baseUrl = $this->server->get( 'PHP_SELF' );
        } elseif ( basename( $this->server->get( 'ORIG_SCRIPT_NAME' ) ) === $filename ) {
            $baseUrl = $this->server->get( 'ORIG_SCRIPT_NAME' ); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path    = $this->server->get( 'PHP_SELF', '' );
            $file    = $this->server->get( 'SCRIPT_FILENAME', '' );
            $segs    = explode( '/', trim( $file, '/' ) );
            $segs    = array_reverse( $segs );
            $index   = 0;
            $last    = \count( $segs );
            $baseUrl = '';
            do {
                $seg     = $segs[ $index ];
                $baseUrl = '/' . $seg . $baseUrl;
                ++ $index;
            } while ( $last > $index && ( false !== $pos = strpos( $path, $baseUrl ) ) && 0 != $pos );
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();
        if ( '' !== $requestUri && '/' !== $requestUri[0] ) {
            $requestUri = '/' . $requestUri;
        }

        if ( $baseUrl && null !== $prefix = $this->getUrlencodedPrefix( $requestUri, $baseUrl ) ) {
            // full $baseUrl matches
            return $prefix;
        }

        if ( $baseUrl && null !== $prefix = $this->getUrlencodedPrefix( $requestUri, rtrim( \dirname( $baseUrl ), '/' . \DIRECTORY_SEPARATOR ) . '/' ) ) {
            // directory portion of $baseUrl matches
            return rtrim( $prefix, '/' . \DIRECTORY_SEPARATOR );
        }

        $truncatedRequestUri = $requestUri;
        if ( false !== $pos = strpos( $requestUri, '?' ) ) {
            $truncatedRequestUri = substr( $requestUri, 0, $pos );
        }

        $basename = basename( $baseUrl );
        if ( empty( $basename ) || ! strpos( rawurldecode( $truncatedRequestUri ) . '/', '/' . $basename . '/' ) ) {
            // strip autoindex filename, for virtualhost based on URL path
            $baseUrl = \dirname( $baseUrl ) . '/';

            $basename = basename( $baseUrl );
            if ( empty( $basename ) || ! strpos( rawurldecode( $truncatedRequestUri ) . '/', '/' . $basename . '/' ) ) {
                // no match whatsoever; set it blank
                return '';
            }
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if ( \strlen( $requestUri ) >= \strlen( $baseUrl ) && ( false !== $pos = strpos( $requestUri, $baseUrl ) ) && 0 !== $pos ) {
            $baseUrl = substr( $requestUri, 0, $pos + \strlen( $baseUrl ) );
        }

        return rtrim( $baseUrl, '/' . \DIRECTORY_SEPARATOR );
    }

    /**
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, null otherwise.
     *
     * @param string $string
     * @param string $prefix
     *
     * @return string|null
     */
    private function getUrlencodedPrefix( string $string, string $prefix ): ?string {
        if ( ! str_starts_with( rawurldecode( $string ), $prefix ) ) {
            return null;
        }

        $len = \strlen( $prefix );

        if ( preg_match( sprintf( '#^(%%[[:xdigit:]]{2}|.){%d}#', $len ), $string, $match ) ) {
            return $match[0];
        }

        return null;
    }

    /**
     * Gets the user info.
     *
     * @return string|null A user name and, optionally, scheme-specific information about how to gain authorization to access the server
     */
    public function getUserInfo(): ?string {
        $userinfo = $this->getUser();

        $pass = $this->getPassword();
        if ( '' != $pass ) {
            $userinfo .= ":$pass";
        }

        return $userinfo;
    }

    /**
     * Returns the user.
     *
     * @return string|null
     */
    public function getUser(): ?string {
        return $this->headers->get( 'PHP_AUTH_USER' );
    }

    /**
     * Returns the password.
     *
     * @return string|null
     */
    public function getPassword(): ?string {
        return $this->headers->get( 'PHP_AUTH_PW' );
    }

    /**
     * Generates a normalized URI (URL) for the Request.
     *
     * @return string A normalized URI (URL) for the Request
     *
     * @see getQueryString()
     */
    public function getUriString(): string {
        if ( null !== $qs = $this->getQueryString() ) {
            $qs = '?' . $qs;
        }

        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $this->getPathInfo() . $qs;
    }

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string|null A normalized query string for the Request
     */
    public function getQueryString(): ?string {
        $qs = static::normalizeQueryString( $this->server->get( 'QUERY_STRING' ) );

        return '' === $qs ? null : $qs;
    }

    /**
     * Gets the scheme and HTTP host.
     *
     * If the URL was called with basic authentication, the user
     * and the password are not added to the generated string.
     *
     * @return string The scheme and HTTP host
     */
    public function getSchemeAndHttpHost(): string {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    public function getScheme(): string {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     *
     * @return string
     */
    public function getHttpHost(): string {
        $scheme = $this->getScheme();
        $port   = (int) $this->getPort();

        if ( ( 'http' === $scheme && 80 === $port ) || ( 'https' === $scheme && 443 === $port ) ) {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }

    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Port" header must contain the client port.
     *
     * @return int|string can be a string if fetched from the server bag
     */
    public function getPort(): int|string {
        if ( $this->isFromTrustedProxy() && $host = $this->getTrustedValues( self::HEADER_X_FORWARDED_PORT ) ) {
            $host = $host[0];
        } elseif ( $this->isFromTrustedProxy() && $host = $this->getTrustedValues( self::HEADER_X_FORWARDED_HOST ) ) {
            $host = $host[0];
        } elseif ( ! $host = $this->headers->get( 'HOST' ) ) {
            return $this->server->get( 'SERVER_PORT' );
        }

        if ( '[' === $host[0] ) {
            $pos = strpos( $host, ':', strrpos( $host, ']' ) );
        } else {
            $pos = strrpos( $host, ':' );
        }

        if ( false !== $pos && $port = substr( $host, $pos + 1 ) ) {
            return (int) $port;
        }

        return 'https' === $this->getScheme() ? 443 : 80;
    }

    /**
     * Returns the host name.
     *
     * This method can read the client host name from the "X-Forwarded-Host" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Host" header must contain the client host name.
     *
     * @return string
     *
     * @throws SuspiciousOperationException when the host name is invalid or not trusted
     */
    public function getHost(): string {
        if ( $this->isFromTrustedProxy() && $host = $this->getTrustedValues( self::HEADER_X_FORWARDED_HOST ) ) {
            $host = $host[0];
        } elseif ( ! $host = $this->headers->get( 'HOST' ) ) {
            if ( ! $host = $this->server->get( 'SERVER_NAME' ) ) {
                $host = $this->server->get( 'SERVER_ADDR', '' );
            }
        }

        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower( preg_replace( '/:\d+$/', '', trim( $host ) ) );

        // as the host can come from the user (HTTP_HOST and depending on the configuration, SERVER_NAME too can come from the user)
        // check that it does not contain forbidden characters (see RFC 952 and RFC 2181)
        // use preg_replace() instead of preg_match() to prevent DoS attacks with long host names
        if ( $host && '' !== preg_replace( '/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host ) ) {
            if ( ! $this->isHostValid ) {
                return '';
            }
            $this->isHostValid = false;

            throw new SuspiciousOperationException( sprintf( 'Invalid Host "%s".', $host ) );
        }

        if ( \count( self::$trustedHostPatterns ) > 0 ) {
            // to avoid host header injection attacks, you should provide a list of trusted host patterns

            if ( in_array( $host, self::$trustedHosts ) ) {
                return $host;
            }

            foreach ( self::$trustedHostPatterns as $pattern ) {
                if ( preg_match( $pattern, $host ) ) {
                    self::$trustedHosts[] = $host;

                    return $host;
                }
            }

            if ( ! $this->isHostValid ) {
                return '';
            }
            $this->isHostValid = false;

            throw new SuspiciousOperationException( sprintf( 'Untrusted Host "%s".', $host ) );
        }

        return $host;
    }

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string|null The raw path (i.e. not urldecoded)
     */
    public function getPathInfo(): ?string {
        if ( null === $this->pathInfo ) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    /**
     * Prepares the path info.
     *
     * @return string path info
     */
    protected function preparePathInfo(): ?string {
        if ( null === ( $requestUri = $this->getRequestUri() ) ) {
            return '/';
        }

        // Remove the query string from REQUEST_URI
        if ( false !== $pos = strpos( $requestUri, '?' ) ) {
            $requestUri = substr( $requestUri, 0, $pos );
        }
        if ( '' !== $requestUri && '/' !== $requestUri[0] ) {
            $requestUri = '/' . $requestUri;
        }

        if ( null === ( $baseUrl = $this->getBaseUrlReal() ) ) {
            return $requestUri;
        }

        $pathInfo = substr( $requestUri, \strlen( $baseUrl ) );
        if ( false === $pathInfo || '' === $pathInfo ) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        }

        return (string) $pathInfo;
    }

    /**
     * Generates a normalized URI for the given path.
     *
     * @param string $path A path to use instead of the current one
     *
     * @return string The normalized URI for the path
     */
    public function getUriForPath( string $path ): string {
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $path;
    }

    /**
     * Returns the path as relative reference from the current Request path.
     *
     * Only the URIs path component (no schema, host etc.) is relevant and must be given.
     * Both paths must be absolute and not contain relative parts.
     * Relative URLs from one resource to another are useful when generating self-contained downloadable document archives.
     * Furthermore, they can be used to reduce the link size in documents.
     *
     * Example target paths, given a base path of "/a/b/c/d":
     * - "/a/b/c/d"     -> ""
     * - "/a/b/c/"      -> "./"
     * - "/a/b/"        -> "../"
     * - "/a/b/c/other" -> "other"
     * - "/a/x/y"       -> "../../x/y"
     *
     * @param string $path
     *
     * @return string The relative target path
     */
    public function getRelativeUriForPath( string $path ): string {
        // be sure that we are dealing with an absolute path
        if ( ! isset( $path[0] ) || '/' !== $path[0] ) {
            return $path;
        }

        if ( $path === $basePath = $this->getPathInfo() ) {
            return '';
        }

        $sourceDirs = explode( '/', isset( $basePath[0] ) && '/' === $basePath[0] ? substr( $basePath, 1 ) : $basePath );
        $targetDirs = explode( '/', substr( $path, 1 ) );
        array_pop( $sourceDirs );
        $targetFile = array_pop( $targetDirs );

        foreach ( $sourceDirs as $i => $dir ) {
            if ( isset( $targetDirs[ $i ] ) && $dir === $targetDirs[ $i ] ) {
                unset( $sourceDirs[ $i ], $targetDirs[ $i ] );
            } else {
                break;
            }
        }

        $targetDirs[] = $targetFile;
        $path         = str_repeat( '../', \count( $sourceDirs ) ) . implode( '/', $targetDirs );

        // A reference to the same base directory or an empty subdirectory must be prefixed with "./".
        // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
        // as the first segment of a relative-path reference, as it would be mistaken for a scheme name
        // (see https://tools.ietf.org/html/rfc3986#section-4.2).
        return ! isset( $path[0] ) || '/' === $path[0]
               || ( false !== ( $colonPos = strpos( $path, ':' ) ) && ( $colonPos < ( $slashPos = strpos( $path, '/' ) ) || false === $slashPos ) )
            ? "./$path" : $path;
    }

    /**
     * Gets the "real" request method.
     *
     * @return string The request method
     *
     * @see getMethod()
     */
    public function getRealMethod(): string {
        return strtoupper( $this->server->get( 'REQUEST_METHOD', 'GET' ) );
    }

    /**
     * Gets the mime type associated with the format.
     *
     * @param string $format
     *
     * @return string|null The associated mime type (null if not found)
     */
    public function getMimeType( string $format ): ?string {
        if ( null === static::$formats ) {
            static::initializeFormats();
        }

        return isset( static::$formats[ $format ] ) ? static::$formats[ $format ][0] : null;
    }

    /**
     * Initializes HTTP request formats.
     */
    protected static function initializeFormats(): void {
        static::$formats = [
            'html'   => [ 'text/html', 'application/xhtml+xml' ],
            'txt'    => [ 'text/plain' ],
            'js'     => [ 'application/javascript', 'application/x-javascript', 'text/javascript' ],
            'css'    => [ 'text/css' ],
            'json'   => [ 'application/json', 'application/x-json' ],
            'jsonld' => [ 'application/ld+json' ],
            'xml'    => [ 'text/xml', 'application/xml', 'application/x-xml' ],
            'rdf'    => [ 'application/rdf+xml' ],
            'atom'   => [ 'application/atom+xml' ],
            'rss'    => [ 'application/rss+xml' ],
            'form'   => [ 'application/x-www-form-urlencoded' ],
        ];
    }

    /**
     * Gets the format associated with the request.
     *
     * @return string|null The format (null if no content type is present)
     */
    public function getContentType(): ?string {
        return $this->getFormat( $this->headers->get( 'CONTENT_TYPE' ) );
    }

    /**
     * Gets the format associated with the mime type.
     *
     * @param string|null $mimeType
     *
     * @return string|null The format (null if not found)
     */
    public function getFormat( ?string $mimeType ): ?string {
        $canonicalMimeType = null;
        if ( false !== $pos = strpos( $mimeType, ';' ) ) {
            $canonicalMimeType = trim( substr( $mimeType, 0, $pos ) );
        }

        if ( null === static::$formats ) {
            static::initializeFormats();
        }

        foreach ( static::$formats as $format => $mimeTypes ) {
            if ( in_array( $mimeType, (array) $mimeTypes, true ) ) {
                return $format;
            }
            if ( null !== $canonicalMimeType && in_array( $canonicalMimeType, (array) $mimeTypes, true ) ) {
                return $format;
            }
        }

        return null;
    }

    /**
     * Associates a format with mime types.
     *
     * @param string|null $format
     * @param string|array $mimeTypes The associated mime types (the preferred one must be the first as it will be used as the content type)
     *
     * @return Request
     */
    public function setFormat( ?string $format, array|string $mimeTypes ): static {
        if ( null === static::$formats ) {
            static::initializeFormats();
        }

        static::$formats[ $format ] = \is_array( $mimeTypes ) ? $mimeTypes : [ $mimeTypes ];

        return $this;
    }

    /**
     * Get the default locale.
     *
     * @return string
     */
    public function getDefaultLocale(): string {
        return $this->defaultLocale;
    }

    /**
     * Sets the default locale.
     *
     * @param string $locale
     *
     * @return Request
     */
    public function setDefaultLocale( string $locale ): static {
        $this->defaultLocale = $locale;

        if ( null === $this->locale ) {
            $this->setPhpDefaultLocale( $locale );
        }

        return $this;
    }

    private function setPhpDefaultLocale( string $locale ): void {
        // if either the class Locale doesn't exist, or an exception is thrown when
        // setting the default locale, the intl module is not installed, and
        // the call can be ignored:
        try {
            if ( class_exists( 'Locale', false ) ) {
                \Locale::setDefault( $locale );
            }
        } catch ( \Exception $e ) {
        }
    }

    /**
     * Get the locale.
     *
     * @return string
     */
    public function getLocale(): string {
        return $this->locale ?? $this->defaultLocale;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale
     *
     * @return Request
     */
    public function setLocale( string $locale ): static {
        $this->setPhpDefaultLocale( $this->locale = $locale );

        return $this;
    }

    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc)
     *
     * @return bool
     */
    public function isMethod( string $method ): bool {
        return $this->getMethod() === strtoupper( $method );
    }

    /**
     * Checks whether or not the method is safe.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.2.1
     *
     * @return bool
     */
    public function isMethodSafe(): bool {
        return in_array( $this->getMethod(), [ 'GET', 'HEAD', 'OPTIONS', 'TRACE' ] );
    }

    /**
     * Checks whether or not the method is idempotent.
     *
     * @return bool
     */
    public function isMethodIdempotent(): bool {
        return in_array( $this->getMethod(), [ 'HEAD', 'GET', 'PUT', 'DELETE', 'TRACE', 'OPTIONS', 'PURGE' ] );
    }

    /**
     * Checks whether the method is cacheable or not.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.2.3
     *
     * @return bool True for GET and HEAD, false otherwise
     */
    public function isMethodCacheable(): bool {
        return in_array( $this->getMethod(), [ 'GET', 'HEAD' ] );
    }

    /**
     * Returns the protocol version.
     *
     * If the application is behind a proxy, the protocol version used in the
     * requests between the client and the proxy and between the proxy and the
     * server might be different. This returns the former (from the "Via" header)
     * if the proxy is trusted (see "setTrustedProxies()"), otherwise it returns
     * the latter (from the "SERVER_PROTOCOL" server parameter).
     *
     * @return string
     */
    public function getProtocolVersion(): string {
        if ( $this->isFromTrustedProxy() ) {
            preg_match( '~^(HTTP/)?([1-9]\.[0-9]) ~', $this->headers->get( 'Via' ), $matches );

            if ( $matches ) {
                return 'HTTP/' . $matches[2];
            }
        }

        return $this->server->get( 'SERVER_PROTOCOL' );
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (https://framework.zend.com/license).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (https://www.zend.com/)
     */

    /**
     * Gets the request body decoded as array, typically from a JSON payload.
     *
     * @return array
     * @throws JsonException When the body cannot be decoded to an array
     *
     */
    public function toArray(): array {
        if ( '' === $content = $this->getContent() ) {
            throw new JsonException( 'Response body is empty.' );
        }

        try {
            $content = json_decode( $content, true, 512, \JSON_BIGINT_AS_STRING | ( \PHP_VERSION_ID >= 70300 ? \JSON_THROW_ON_ERROR : 0 ) );
        } catch ( \JsonException $e ) {
            throw new JsonException( 'Could not decode request body.', $e->getCode(), $e );
        }

        if ( \PHP_VERSION_ID < 70300 && \JSON_ERROR_NONE !== json_last_error() ) {
            throw new JsonException( 'Could not decode request body: ' . json_last_error_msg(), json_last_error() );
        }

        if ( ! \is_array( $content ) ) {
            throw new JsonException( sprintf( 'JSON content was expected to decode to an array, "%s" returned.', get_debug_type( $content ) ) );
        }

        return $content;
    }

    /**
     * Gets the Etags.
     *
     * @return array The entity tags
     */
    public function getETags(): array {
        return preg_split( '/\s*,\s*/', $this->headers->get( 'if_none_match' ), null, \PREG_SPLIT_NO_EMPTY );
    }

    /**
     * @return bool
     */
    public function isNoCache(): bool {
        return $this->headers->hasCacheControlDirective( 'no-cache' ) || 'no-cache' === $this->headers->get( 'Pragma' );
    }

    /**
     * Gets the preferred format for the response by inspecting, in the following order:
     *   * the request format set using setRequestFormat;
     *   * the values of the Accept HTTP header.
     *
     * Note that if you use this method, you should send the "Vary: Accept" header
     * in the response to prevent any issues with intermediary HTTP caches.
     *
     * @param string|null $default
     *
     * @return string|null
     */
    public function getPreferredFormat( ?string $default = 'html' ): ?string {
        if ( null !== $this->preferredFormat || null !== $this->preferredFormat = $this->getRequestFormat( null ) ) {
            return $this->preferredFormat;
        }

        foreach ( $this->getAcceptableContentTypes() as $mimeType ) {
            if ( $this->preferredFormat = $this->getFormat( $mimeType ) ) {
                return $this->preferredFormat;
            }
        }

        return $default;
    }

    /**
     * Gets a list of content types acceptable by the client browser.
     *
     * @return array|null List of content types in preferable order
     */
    public function getAcceptableContentTypes(): ?array {
        if ( null !== $this->acceptableContentTypes ) {
            return $this->acceptableContentTypes;
        }

        return $this->acceptableContentTypes = array_keys( AcceptHeader::fromString( $this->headers->get( 'Accept' ) )->all() );
    }

    /**
     * Returns the preferred language.
     *
     * @param string[] $locales An array of ordered available locales
     *
     * @return string|null The preferred locale
     */
    public function getPreferredLanguage( array $locales = null ): ?string {
        $preferredLanguages = $this->getLanguages();

        if ( empty( $locales ) ) {
            return $preferredLanguages[0] ?? null;
        }

        if ( ! $preferredLanguages ) {
            return $locales[0];
        }

        $extendedPreferredLanguages = [];
        foreach ( $preferredLanguages as $language ) {
            $extendedPreferredLanguages[] = $language;
            if ( false !== $position = strpos( $language, '_' ) ) {
                $superLanguage = substr( $language, 0, $position );
                if ( ! in_array( $superLanguage, $preferredLanguages, true ) ) {
                    $extendedPreferredLanguages[] = $superLanguage;
                }
            }
        }

        $preferredLanguages = array_values( array_intersect( $extendedPreferredLanguages, $locales ) );

        return $preferredLanguages[0] ?? $locales[0];
    }

    /**
     * Gets a list of languages acceptable by the client browser.
     *
     * @return array Languages ordered in the user browser preferences
     */
    public function getLanguages(): array {
        if ( null !== $this->languages ) {
            return $this->languages;
        }

        $languages       = AcceptHeader::fromString( $this->headers->get( 'Accept-Language' ) )->all();
        $this->languages = [];
        foreach ( $languages as $lang => $acceptHeaderItem ) {
            if ( str_contains( $lang, '-' ) ) {
                $codes = explode( '-', $lang );
                if ( 'i' === $codes[0] ) {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registered with the
                    // i-prefix, such as i-cherokee
                    if ( \count( $codes ) > 1 ) {
                        $lang = $codes[1];
                    }
                } else {
                    foreach ( $codes as $i => $iValue ) {
                        if ( 0 === $i ) {
                            $lang = strtolower( $codes[0] );
                        } else {
                            $lang .= '_' . strtoupper( $iValue );
                        }
                    }
                }
            }

            $this->languages[] = $lang;
        }

        return $this->languages;
    }

    /**
     * Gets a list of charsets acceptable by the client browser.
     *
     * @return array|null List of charsets in preferable order
     */
    public function getCharsets(): ?array {
        return $this->charsets ?? ( $this->charsets = array_keys( AcceptHeader::fromString( $this->headers->get( 'Accept-Charset' ) )->all() ) );
    }

    /**
     * Gets a list of encodings acceptable by the client browser.
     *
     * @return array|null List of encodings in preferable order
     */
    public function getEncodings(): ?array {
        return $this->encodings ?? ( $this->encodings = array_keys( AcceptHeader::fromString( $this->headers->get( 'Accept-Encoding' ) )->all() ) );
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * It works if your JavaScript library sets an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     *
     * @see https://wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return bool true if the request is an XMLHttpRequest, false otherwise
     */
    public function isXmlHttpRequest(): bool {
        return 'XMLHttpRequest' === $this->headers->get( 'X-Requested-With' );
    }

    /**
     * Checks whether the client browser prefers safe content or not according to RFC8674.
     *
     * @see https://tools.ietf.org/html/rfc8674
     */
    public function preferSafeContent(): bool {
        if ( null !== $this->isSafeContentPreferred ) {
            return $this->isSafeContentPreferred;
        }

        if ( ! $this->isSecure() ) {
            // see https://tools.ietf.org/html/rfc8674#section-3
            $this->isSafeContentPreferred = false;

            return $this->isSafeContentPreferred;
        }

        $this->isSafeContentPreferred = AcceptHeader::fromString( $this->headers->get( 'Prefer' ) )->has( 'safe' );

        return $this->isSafeContentPreferred;
    }

    private function decodeInput(): array {
        $input = file_get_contents('php://input');

        if (is_string($input) && !empty($input)) {
            $input = json_decode( $input, true, 512, JSON_THROW_ON_ERROR );
        }

        return is_array($input) ? $input : array();
    }

    /**
     *
     *
     * @return array
     * @throws \GraphQL\Error\SyntaxError
     * @throws \JsonException
     */
    private function getGraphQlStructure(): array {
        $parsed = array(
            'query' => $this->getContent()->get( key: 'query' ),
            'mutation' => $this->getContent()->get( key: 'mutation' ),
            'variables' => $this->getContent()->get( key: 'variables' ),
        );

        $parser = new Parser($this->getContent()->get( key: 'query' ));

        if (!empty($this->getContent()->get( key: 'query' ))) {
            $parsedContent = $parser::parse($this->getContent()->get( key: 'query' ));
            foreach ($parsedContent->definitions as /** @var \GraphQL\Language\AST\OperationDefinitionNode */ $definition) {
                $selections = $definition->selectionSet->selections->getIterator();
                foreach ($selections as $selection) {
                    $arguments = $selection->arguments->getIterator();
                    $parsed[$selection->name->value] = array();
                    foreach ($arguments as $argument) {
                        if (!isset($argument->value->fields) || empty($argument->value->fields)) {
                            if (!isset($argument->value->name->value) && !isset($argument->value->value)) {
                                continue;
                            }
                            $parsed[$selection->name->value][$argument->name->value] = $argument->value->kind === 'Variable' ?
                                $parsed['variables'][$argument->value->name->value] : $argument->value->value;
                            continue;
                        }
                        $fields = $argument->value->fields->getIterator();
                        foreach ($fields as $field) {
                            if (!isset($field->value->name->value) && !isset($field->value->value)) {
                                continue;
                            }
                            $parsed[$selection->name->value][$field->name->value] = $field->value->kind === 'Variable' ?
                                $parsed['variables'][$field->value->name->value] : $field->value->value;
                        }
                    }
                }
            }
        }

        if (!empty($this->getContent()->get( key: 'mutation' ))) {
            $parsedContent = $parser::parse($this->getContent()->get( key: 'mutation' ));
            foreach ($parsedContent->definitions as /** @var \GraphQL\Language\AST\OperationDefinitionNode */ $definition) {
                $selections = $definition->selectionSet->selections->getIterator();
                foreach ($selections as $selection) {
                    $arguments = $selection->arguments->getIterator();
                    $parsed[$selection->name->value] = array();
                    foreach ($arguments as $argument) {
                        if (!isset($argument->value->fields) || empty($argument->value->fields)) {
                            continue;
                        }
                        $fields = $argument->value->fields->getIterator();
                        foreach ($fields as $field) {
                            if (!isset($field->value->name->value) && !isset($field->value->value)) {
                                continue;
                            }
                            $parsed[$selection->name->value][$field->name->value] = $field->value->kind === 'Variable' ?
                                $parsed['variables'][$field->value->name->value] : $field->value->value;
                        }
                    }
                }
            }
        }

        return $parsed;
    }

    /**
     * Sets the data to be sent as JSON.
     *
     * @param mixed $data
     *
     * @return string
     *
     * @throws \InvalidArgumentException|\JsonException
     */
    private function jsonEncode( $data = array() ): string {
        if (empty($data)) {
            return '';
        }
        try {
            $data = json_encode( $data, JSON_THROW_ON_ERROR | 15 );
        } catch ( \Exception $e ) {
            if ( 'Exception' === \get_class( $e ) && str_starts_with( $e->getMessage(), 'Failed calling ' ) ) {
                throw $e->getPrevious() ?: $e;
            }
            throw $e;
        }

        if ( \JSON_THROW_ON_ERROR & 15 ) {
            return $data;
        }

        if ( \JSON_ERROR_NONE !== json_last_error() ) {
            throw new \InvalidArgumentException( json_last_error_msg() );
        }

        return $data;
    }
}
