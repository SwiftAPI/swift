<?php declare(strict_types=1);

namespace Swift\Router;

class HTTPRequest {

	/**
	 * @var Request $request
	 */
	public Request $request;

	/**
	 * HTTPRequest constructor.
	 */
	public function __construct(
			Request $request
	) {
		$this->request      = $request;
	}


}