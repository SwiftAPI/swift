<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Http\Response;

use Exception;
use JetBrains\PhpStorm\Deprecated;
use Swift\HttpFoundation\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class Response
 * @package Swift\Http\Response
 */
#[Deprecated( replacement: \Swift\HttpFoundation\Response::class )]
abstract class Response extends SymfonyResponse implements ResponseInterface {

    /**
     * @var string $defaultResponse
     */
    private static string $defaultResponse;
    /**
     * @var mixed
     */
    protected mixed $response;

    public static function notAuthorized(): void {
        self::$defaultResponse = 'notAuthorized';
    }

    public static function accessDenied(): void {
        self::$defaultResponse = 'notAuthorized';
    }

    public static function notFound(): void {
        self::$defaultResponse = 'notFound';
    }

    /**
     * Method to get response
     *
     * @return mixed
     */
    public function getResponse(): mixed {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse( mixed $response ): void {
        $this->response = $response;
    }

    /**
     * Backward compatibility
     *
     * @return $this
     */
    public function send(): static {
        $this->sendOutput();

        return $this;
    }

    /**
     * Wrapper method for doOutput()
     */
    public function sendOutput(): void {
        header( "Access-Control-Allow-Origin: *" );
        header( "Access-Control-Allow-Credentials: true" );
        header( "Cache-Control: no-cache" );
        header( "Pragma: no-cache" );
        header( "Vary: Accept-Encoding, Origin" );
        header( "Keep-Alive: timeout=2, max=100" );
        header( "Connection: Keep-Alive" );
        header( "Content-Type: text/plain" );

        try {
            if ( isset( self::$defaultResponse ) ) {
                $methodName = 'response' . ucfirst( self::$defaultResponse );
                if ( method_exists( $this, $methodName ) ) {
                    $this->{$methodName}();
                } else {
                    throw new Exception( 'Default response does not exist', 500 );
                }
            } else {
                if ( ! isset( $this->response ) ) {
                    throw new Exception( 'No output', 500 );
                }

                $this->doOutput();
            }
        } catch ( Exception $exception ) {
            self::internalError();
            $this->{self::$defaultResponse}();
        }
    }

    abstract protected function doOutput(): void;

    public static function internalError(): void {
        self::$defaultResponse = 'internalError';
    }

    protected function responseNotAuthorized(): void {
        header( "HTTP/1.1 401 Unauthorized" );
        header( 'Status:' . 401 );
        header( 'Message: Not authorized' );
    }

    protected function responseAccessDenied( string $message = '' ): void {
        header( "HTTP/1.1 403 Unauthorized" );
        header( 'Status:' . 403 );
        header( 'Message: Not authorized: ' . $message );
    }

    protected function responseNotFound(): void {
        header( "HTTP/1.1 404 Not found" );
        header( 'Status:' . 404 );
        header( 'Message: Not found' );
    }

    protected function responseInternalError(): void {
        header( "HTTP/1.1 500 Internal error" );
        header( 'Status:' . 500 );
        header( 'Message: Internal error' );
    }


}