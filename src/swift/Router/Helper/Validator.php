<?php declare(strict_types=1);

namespace Swift\Router\Helper;

use Swift\Router\HTTPRequest;

class Validator {

	/**
	 * @var HTTPRequest $HTTPRequest
	 */
	private HTTPRequest $HTTPRequest;

	/**
	 * Validator constructor.
	 */
	public function __construct(
		HTTPRequest $HTTPRequest
	) {
		$this->HTTPRequest  = $HTTPRequest;
	}

	/**
	 * Method to validate whether the request being made is valid
	 *
	 * @return bool
	 */
	public function requestIsValid() : bool {
		// Validate whether request is valid
		// TODO: Validation

		return true;
	}


}