<?php declare(strict_types=1);

namespace Swift\Router;


class Input {

	/**
	 * @var array $input
	 */
	protected $input = array();

	/**
	 * Input constructor.
	 */
	public function __construct() {
		$this->getInput();
	}

	/**
	 * Method to get input
	 *
	 * @return void
	 */
	private function getInput() : void {
		$input = @file_get_contents('php://input');

		// Check if input is json encoded
		$decode = json_decode($input, true);
		if (json_last_error() == JSON_ERROR_NONE) {
			$input = $decode;
		}

		if (!is_array($input)) {
			$input = (array) $input;
		}

		$get = $_SERVER['QUERY_STRING'] ?? '';
		$get = explode('&', $get);
		foreach ($get as $item) {
			$item = explode('=', $item);
			if (count($item) !== 2) {
				continue;
			}
			$this->input[$item[0]] = $item[1];
		}

		$this->input = array_merge($this->input, $input);
	}

	/**
	 * Method to get specific input
	 *
	 * @return mixed  input value on found. false on input not found
	 */
	public function get(string $key) {
		if (!array_key_exists($key, $this->input)) {
			return false;
		}

		return $this->input[$key];
	}

	/**
	 * Method to get input
	 *
	 * @return array
	 */
	public function getArray() : array {
		return $this->input;
	}

}