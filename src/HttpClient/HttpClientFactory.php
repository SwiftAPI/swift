<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpClient;


use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleRetry\GuzzleRetryMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\HttpClient\Exceptions\HttpClientAlreadyExistsException;

/**
 * Class HttpClientFactory
 * @package Swift\HttpClient
 */
#[Autowire]
final class HttpClientFactory implements ClientInterface {
    
    private array $clients = [];
    private HandlerStack $handlerStack;
    
    /**
     * HttpClientFactory constructor.
     */
    public function __construct(
        MiddlewareHandler      $middlewareHandler,
        RetryMiddlewareHandler $retryMiddlewareHandler,
    ) {
        $this->handlerStack = HandlerStack::create();
        
        function middleware( $middlewareHandler ): \Closure {
            return static function ( callable $handler ) use ( $middlewareHandler ) {
                return static function ( RequestInterface $request, array $options ) use ( $handler, $middlewareHandler ) {
                    [ $request, $options ] = $middlewareHandler->handle( $request, $options );
                    
                    return $handler( $request, $options );
                };
            };
        }
        
        $retryMiddleware = static function ( int $attemptNumber, float $delay, RequestInterface &$request, array $options, ResponseInterface|null $response ) use ( $retryMiddlewareHandler ) {
            $retryMiddlewareHandler->handle( $attemptNumber, $delay, $request, $options, $response );
        };
        
        $this->handlerStack->push( middleware( $middlewareHandler ) );
        $this->handlerStack->push(
            GuzzleRetryMiddleware::factory( [
                                                'retry_on_status'    => [ 401, 429, 503, 500 ],
                                                'max_retry_attempts' => 2,
                                                'on_retry_callback'  => $retryMiddleware,
                                            ] )
        );
    }
    
    /**
     * Create a client for a given uri
     *
     * @param string|null $uri
     * @param bool        $useCache
     *
     * @return HttpClient
     */
    public function createForBaseUri( ?string $uri = null, bool $useCache = true ): HttpClient {
        if ( $uri && array_key_exists( $uri, $this->clients ) ) {
            throw new HttpClientAlreadyExistsException( sprintf( 'Client with base uri %s has already been defined', $uri ) );
        }
        
        $client = new HttpClient( [
                                      'handler' => $this->handlerStack,
                                  ] );
        
        if ( $useCache && ! is_null( $uri ) ) {
            $this->clients[ $uri ] = $client;
        }
        
        return $client;
    }
    
    /**
     * Get cached client for given uri/regex
     *
     * @param string $uri
     *
     * @return HttpClient|null
     */
    public function getClientForUri( string $uri ): ?HttpClient {
        foreach ( $this->clients as $clientUri => $client ) {
            if ( preg_match( $clientUri, $uri ) ) {
                return $client;
            }
        }
        
        return null;
    }
    
    
    /**
     * @inheritDoc
     */
    public function send( RequestInterface $request, array $options = [] ): ResponseInterface {
        $uri    = '';
        $client = $this->getClientForUri( $uri ) ?? $this->createForBaseUri( $uri, false );
        
        return $client->send( $request, $options );
    }
    
    /**
     * @inheritDoc
     */
    public function sendAsync( RequestInterface $request, array $options = [] ): PromiseInterface {
        // TODO: Implement sendAsync() method.
    }
    
    /**
     * @inheritDoc
     */
    public function request( string $method, $uri, array $options = [] ): ResponseInterface {
        $client = $this->getClientForUri( $uri ) ?? $this->createForBaseUri( $uri, false );
        
        return $client->request( $method, $uri, $options );
    }
    
    /**
     * @inheritDoc
     */
    public function requestAsync( string $method, $uri, array $options = [] ): PromiseInterface {
        // TODO: Implement requestAsync() method.
    }
    
    /**
     * @inheritDoc
     */
    public function getConfig( ?string $option = null ) {
        // TODO: Implement getConfig() method.
    }
    
    
}