<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Http\Request;

use Swift\Configuration\Configuration;
use Swift\Http\Request\Exceptions\RequestFailed;
use Swift\Http\Request\Method;
use Swift\Kernel\Attributes\Autowire;
use Unirest\Request as UnirestRequest;

/**
 * Class Request
 * @package Swift\Http\Request
 */
#[Autowire]
class Request {
    
    private $jsonOpts = array();

    /**
     * @var Configuration $configuration
     */
    private $configuration;

    /**
     * Request constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct( Configuration $configuration ) {
        $this->configuration = $configuration;

        if ($this->configuration->get('app.debug')) {
            UnirestRequest::verifyPeer(false);
            UnirestRequest::verifyHost(false);
        }
    }
    
    /**
     * Set JSON decode mode
     *
     * @param bool $assoc When TRUE, returned objects will be converted into associative arrays.
     * @param integer $depth User specified recursion depth.
     * @param integer $options Bitmask of JSON decode options. Currently only JSON_BIGINT_AS_STRING is supported (default is to cast large integers as floats)
     * @return array
     */
    public function jsonOpts($assoc = false, $depth = 512, $options = 0) {
        $this->jsonOpts = UnirestRequest::jsonOpts($assoc, $depth, $options);
        return $this->jsonOpts;
    }

    /**
     * Verify SSL peer
     *
     * @param bool $enabled enable SSL verification, by default is true
     * @return bool
     */
    public function verifyPeer($enabled) {
        return UnirestRequest::verifyPeer($enabled);
    }

    /**
     * Verify SSL host
     *
     * @param bool $enabled enable SSL host verification, by default is true
     * @return bool
     */
    public function verifyHost($enabled) {
        return UnirestRequest::verifyHost($enabled);
    }

    /**
     * Set a timeout
     *
     * @param integer $seconds timeout value in seconds
     * @return integer
     */
    public function timeout($seconds) {
        return UnirestRequest::timeout($seconds);
    }

    /**
     * Set default headers to send on every request
     *
     * @param array $headers headers array
     * @return array
     */
    public function defaultHeaders($headers) {
        return UnirestRequest::defaultHeader($headers);
    }

    /**
     * Set a new default header to send on every request
     *
     * @param string $name header name
     * @param string $value header value
     * @return string
     */
    public function defaultHeader($name, $value) {
        return UnirestRequest::defaultHeader($name, $value);
    }

    /**
     * Clear all the default headers
     */
    public function clearDefaultHeaders() {
        return UnirestRequest::clearDefaultHeaders();
    }

    /**
     * Set curl options to send on every request
     *
     * @param array $options options array
     * @return array
     */
    public function curlOpts($options) {
        return UnirestRequest::curlOpts($options);
    }

    /**
     * Set a new default header to send on every request
     *
     * @param string $name header name
     * @param string $value header value
     * @return string
     */
    public function curlOpt($name, $value) {
        return UnirestRequest::curlOpt($name, $value);
    }

    /**
     * Clear all the default headers
     */
    public function clearCurlOpts() {
        return UnirestRequest::clearCurlOpts();
    }

    /**
     * Set a Mashape key to send on every request as a header
     * Obtain your Mashape key by browsing one of your Mashape applications on https://www.mashape.com
     *
     * Note: Mashape provides 2 keys for each application: a 'Testing' and a 'Production' one.
     *       Be aware of which key you are using and do not share your Production key.
     *
     * @param string $key Mashape key
     * @return string
     */
    public function setMashapeKey($key) {
        return UnirestRequest::setMashapeKey($key);
    }

    /**
     * Set a cookie string for enabling cookie handling
     *
     * @param string $cookie
     */
    public function cookie($cookie) {
        UnirestRequest::cookie($cookie);
    }

    /**
     * Set a cookie file path for enabling cookie handling
     *
     * $cookieFile must be a correct path with write permission
     *
     * @param string $cookieFile - path to file for saving cookie
     */
    public function cookieFile($cookieFile) {
        UnirestRequest::cookieFile($cookieFile);
    }

    /**
     * Set authentication method to use
     *
     * @param string $username authentication username
     * @param string $password authentication password
     * @param integer $method authentication method
     */
    public function auth($username = '', $password = '', $method = CURLAUTH_BASIC) {
        UnirestRequest::auth($username, $password, $method);
    }

    /**
     * Set proxy to use
     *
     * @param string $address proxy address
     * @param integer $port proxy port
     * @param integer $type (Available options for this are CURLPROXY_HTTP, CURLPROXY_HTTP_1_0 CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A and CURLPROXY_SOCKS5_HOSTNAME)
     * @param bool $tunnel enable/disable tunneling
     */
    public function proxy($address, $port = 1080, $type = CURLPROXY_HTTP, $tunnel = false) {
        UnirestRequest::proxy($address, $port, $type, $tunnel);
    }

    /**
     * Set proxy authentication method to use
     *
     * @param string $username authentication username
     * @param string $password authentication password
     * @param integer $method authentication method
     */
    public function proxyAuth($username = '', $password = '', $method = CURLAUTH_BASIC) {
        UnirestRequest::proxyAuth($username, $password);
    }

    /**
     * Send a GET request to a URL
     *
     * @param string $url URL to send the GET request to
     * @param array $headers additional headers to send
     * @param mixed $parameters parameters to send in the querystring
     * @return Response
     */
    public function get($url, $headers = array(), $parameters = null) {
        return $this->send(Method::GET,  $url, $parameters, $headers);
    }

    /**
     * Send a HEAD request to a URL
     * @param string $url URL to send the HEAD request to
     * @param array $headers additional headers to send
     * @param mixed $parameters parameters to send in the querystring
     * @return Response
     */
    public function head($url, $headers = array(), $parameters = null) {
        return $this->send(Method::HEAD, $url, $parameters, $headers);
    }

    /**
     * Send a OPTIONS request to a URL
     * @param string $url URL to send the OPTIONS request to
     * @param array $headers additional headers to send
     * @param mixed $parameters parameters to send in the querystring
     * @param string $username Basic Authentication username
     * @param string $password Basic Authentication password
     * @return Response
     */
    public function options($url, $headers = array(), $parameters = null, $username = null, $password = null) {
        return $this->send(Method::OPTIONS, $url, $parameters, $headers, $username, $password);
    }

    /**
     * Send a CONNECT request to a URL
     * @param string $url URL to send the CONNECT request to
     * @param array $headers additional headers to send
     * @param mixed $parameters parameters to send in the querystring
     * @return Response
     */
    public function connect($url, $headers = array(), $parameters = null) {
        return $this->send(Method::CONNECT, $url, $parameters, $headers);
    }

    /**
     * Send POST request to a URL
     * @param string $url URL to send the POST request to
     * @param array $headers additional headers to send
     * @param mixed $body POST body data
     * @return Response response
     */
    public function post($url, $headers = array(), $body = null) {
        return $this->send(Method::POST, $url, $body, $headers);
    }

    /**
     * Send DELETE request to a URL
     * @param string $url URL to send the DELETE request to
     * @param array $headers additional headers to send
     * @param mixed $body DELETE body data
     * @return Response
     */
    public function delete($url, $headers = array(), $body = null) {
        return $this->send(Method::DELETE, $url, $body, $headers);
    }

    /**
     * Send PUT request to a URL
     * @param string $url URL to send the PUT request to
     * @param array $headers additional headers to send
     * @param mixed $body PUT body data
     * @return Response
     */
    public function put($url, $headers = array(), $body = null) {
        return $this->send(Method::PUT, $url, $body, $headers);
    }

    /**
     * Send PATCH request to a URL
     * @param string $url URL to send the PATCH request to
     * @param array $headers additional headers to send
     * @param mixed $body PATCH body data
     * @return Response
     */
    public function patch($url, $headers = array(), $body = null) {
        return $this->send(Method::PATCH, $url, $body, $headers);
    }

    /**
     * Send TRACE request to a URL
     * @param string $url URL to send the TRACE request to
     * @param array $headers additional headers to send
     * @param mixed $body TRACE body data
     * @return Response
     */
    public function trace($url, $headers = array(), $body = null) {
        return $this->send(Method::TRACE, $url, $body, $headers);
    }

    /**
     * This function is useful for serializing multidimensional arrays, and avoid getting
     * the 'Array to string conversion' notice
     * @param array|object $data array to flatten.
     * @param bool|string $parent parent key or false if no parent
     * @return array
     */
    public function buildHTTPCurlQuery($data, $parent = false) {
        return UnirestRequest::buildHTTPCurlQuery($data, $parent);
    }

    /**
     * Send a cURL request
     * @param \Unirest\Method|string $method HTTP method to use
     * @param string $url URL to send the request to
     * @param mixed $body request body
     * @param array $headers additional headers to send
     * @param string $username Authentication username (deprecated)
     * @param string $password Authentication password (deprecated)
     * @throws RequestFailed if a cURL error occurs
     * @return Response
     */
    public function send($method, $url, $body = null, $headers = array(), $username = null, $password = null): Response {
        try {
            $response = UnirestRequest::send($method, $url, $body, $headers, $username, $password);
            return (new Response($response));
        } catch (Exception $exception) {
            throw new RequestFailed($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Get information regarding a specific request
     * Optionally a handle can be provided
     * @see https://www.php.net/manual/en/function.curl-getinfo.php
     *
     * @param $opt
     *
     * @return mixed
     */
    public function getInfo($opt = false) {
        return UnirestRequest::getInfo($opt);
    }

    public function getCurlHandle() {
        return UnirestRequest::getCurlHandle();
    }

    public function getFormattedHeaders($headers) {
        return UnirestRequest::getFormattedHeaders($headers);
    }

}