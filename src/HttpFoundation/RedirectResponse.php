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
use Swift\Kernel\Attributes\DI;

/**
 * RedirectResponse represents an HTTP response doing a redirect.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
#[DI( exclude: true, autowire: false )]
class RedirectResponse extends Response {

    protected string $targetUrl;

    /**
     * Creates a redirect response so that it conforms to the rules defined for a redirect status code.
     *
     * @param string $url The URL to redirect to. The URL should be a full URL, with schema etc.,
     *                        but practically every browser redirects on paths only as well
     * @param int $status The status code (302 by default)
     * @param array $headers The headers (Location is always set to the given URL)
     *
     * @throws \InvalidArgumentException
     *
     * @see https://tools.ietf.org/html/rfc2616#section-10.3
     */
    public function __construct( string $url, int $status = 302, array $headers = [] ) {
        parent::__construct( $this->createBody( $url )->__toString(), $status, $headers );

        $this->targetUrl = $url;
        $this->headers->set( 'Location', $url );

        if ( ! $this->isRedirect() ) {
            throw new \InvalidArgumentException( sprintf( 'The HTTP status code is not a redirect ("%s" given).', $status ) );
        }

        if ( 301 === $status && ! \array_key_exists( 'cache-control', array_change_key_case( $headers, \CASE_LOWER ) ) ) {
            $this->headers->remove( 'cache-control' );
        }
    }

    private function createBody( string $url ): StreamInterface {
        return Stream::create( sprintf(
            '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8" />
                            <meta http-equiv="refresh" content="0;url=\'%1$s\'" />
                    
                            <title>Redirecting to %1$s</title>
                        </head>
                        <body>
                            Redirecting to <a href="%1$s">%1$s</a>.
                        </body>
                    </html>',
            htmlspecialchars( $url, \ENT_QUOTES, 'UTF-8' ) ) );
    }

    /**
     * Returns the target URL.
     *
     * @return string target URL
     */
    public function getTargetUrl(): string {
        return $this->targetUrl;
    }

    /**
     * Sets the redirect target of this response.
     *
     * @param string $url
     *
     * @return $this
     *
     */
    public function withTargetUrl( string $url ): static {
        $new = clone $this;
        if ( '' === $url ) {
            throw new \InvalidArgumentException( 'Cannot redirect to an empty URL.' );
        }

        $new->targetUrl = $url;

        $new = $new->withBody( $this->createBody( $url ) );

        $new->headers->set( 'Location', $url );

        return $new;
    }
}
