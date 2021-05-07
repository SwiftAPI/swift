<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

use Psr\Http\Message\{RequestInterface as PsrRequestInterface, ServerRequestInterface, StreamInterface, UploadedFileInterface, UriInterface};
use Swift\HttpFoundation\Exception\ConflictingHeadersException;
use Swift\HttpFoundation\Exception\JsonException;
use Swift\HttpFoundation\Exception\SuspiciousOperationException;
use Swift\HttpFoundation\Session\SessionInterface;
use Swift\Kernel\Attributes\DI;
use function in_array;

/**
 * ServerRequest represents an HTTP request.
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
#[DI(aliases: [RequestInterface::class . ' $request', PsrRequestInterface::class . ' $request', ServerRequestInterface::class . ' $request'])]
class ServerRequest extends Request implements ServerRequestInterface {

    /** @var array */
    private array $cookieParams = [];

    /** @var array|object|null */
    private object|null|array $parsedBody;

    /** @var array */
    private array $queryParams = [];

    /** @var array */
    private array $serverParams;

    /** @var UploadedFileInterface[] */
    private array $uploadedFiles = [];

    public function getServerParams(): array {
        return $this->serverParams;
    }

    public function getUploadedFiles(): array {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles( array $uploadedFiles ): static {
        $new                = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    public function getCookieParams(): array {
        return $this->cookieParams;
    }

    public function withCookieParams( array $cookies ): static {
        $new               = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    public function getQueryParams(): array {
        return $this->queryParams;
    }

    public function withQueryParams( array $query ): static {
        $new              = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    public function getParsedBody(): object|array|null {
        return $this->parsedBody;
    }

    public function withParsedBody( $data ): static {
        if ( ! \is_array( $data ) && ! \is_object( $data ) && null !== $data ) {
            throw new \InvalidArgumentException( 'First parameter to withParsedBody MUST be object, array or null' );
        }

        $new             = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    public function getAttribute( $attribute, $default = null ): mixed {
        return $this->attributes->get( $attribute, $default );
    }

    public function withAttribute( $name, $value ): self {
        $new                           = clone $this;
        $new->attributes->set($name, $value);

        return $new;
    }

    public function withoutAttribute( $name ): self {
        if ( !$this->attributes->has($name)) {
            return $this;
        }

        $new = clone $this;
        $new->attributes->remove($name);

        return $new;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return ParameterBag Attributes derived from the request.
     */
    public function getAttributes(): ParameterBag {
        return $this->attributes;
    }
}
