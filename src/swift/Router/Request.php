<?php declare(strict_types=1);

namespace Swift\Router;


class Request {

	/**
	 * @var string  $method
	 */
	protected $method;

	/**
	 * @var string  $uri
	 */
	protected $uri;

	/**
	 * @var array $request
	 */
	protected $request = array();

	/**
	 * @var array $headers
	 */
	protected $headers = array();

	/**
	 * @var Input $input
	 */
	public $input;

	/**
	 * Request constructor.
	 *
	 * @param Input $input
	 */
	public function __construct(
			Input $input
	) {
		$this->input  = $input;

		$this->setMethod();
		$this->setUri();
		$this->setRequest();
		$this->setHeaders();
	}

	/**
	 * Method to set request method
	 *
	 * @return void
	 */
	protected function setMethod() : void {
		$this->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
	}

	/**
	 * Method to get request method
	 *
	 * @return string
	 */
	public function getMethod() : string {
		return $this->method;
	}

	/**
	 * Method to set request uri
	 *
	 * @return void
	 */
	protected function setUri() : void {
		$uri = $_SERVER['REQUEST_URI'] ?? '';
		// Strip query string (?a=b) from Request Url
		if (($strpos = strpos($uri, '?')) !== false) {
			$uri = substr($uri, 0, $strpos);
		}

		// Remove trailing slash if not root url
		if ($uri !== '/' && substr($uri, -1) === '/') {
			$uri = substr($uri, 0, -1);
		}

		$this->uri = $uri;
	}

	/**
	 * Method to get request uri
	 *
	 * @return string
	 */
	public function getUri() : string {
		return $this->uri;
	}

	/**
	 * Method to set request
	 *
	 * @return void
	 */
	protected function setRequest() : void {
		$this->request = $_SERVER;
	}

	/**
	 * Method to get request
	 *
	 * @return array
	 */
	public function getRequest() : array {
		return $this->request;
	}

	/**
	 * Method to set request headers
	 */
	public function setHeaders() : void {
		$this->headers = function_exists('getallheaders') ? getallheaders() : array();
	}

	/**
	 * Method to get request headers
	 *
	 * @return array
	 */
	public function getHeaders(): array {
		return $this->headers;
	}

	/**
	 * Method to get header by name
	 * 
	 * @param string $headerName
	 *
	 * @return string|null
	 */
	public function getHeader(string $headerName) : ?string {
		if (array_key_exists($headerName, $this->headers)) {
			return $this->headers[$headerName];
		} else {
			return null;
		}
	}

}