<?php declare(strict_types=1);

namespace Swift\Http\Response;


use Exception;
use stdClass;

abstract class Response
{

	/**
	 * @var mixed
	 */
	protected mixed $response;

	/**
	 * @var string  $defaultResponse
	 */
	private static string $defaultResponse;

    /**
     * @param mixed $response
     */
	public function setResponse( mixed $response ): void {
		$this->response = $response;
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
	 * Wrapper method for doOutput()
	 */
	public function sendOutput(): void {
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Credentials: true");
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		header("Vary: Accept-Encoding, Origin");
		header("Keep-Alive: timeout=2, max=100");
		header("Connection: Keep-Alive");
		header("Content-Type: text/plain");

		try {
			if (isset(self::$defaultResponse)) {
				$methodName = 'response' . ucfirst(self::$defaultResponse);
				if (method_exists($this, $methodName)) {
					$this->{$methodName}();
				} else {
					throw new Exception('Default response does not exist', 500);
				}
			} else {
				if (!isset($this->response)) {
					throw new Exception('No output', 500);
				}

				$this->doOutput();
			}
		} catch ( Exception $exception) {
			self::internalError();
			$this->{self::$defaultResponse}();
		}
	}

	abstract protected function doOutput() : void;

	public static function notAuthorized() : void {
		self::$defaultResponse  = 'notAuthorized';
	}

	protected function responseNotAuthorized() : void {
		header("HTTP/1.1 401 Unauthorized");
		header('Status:' . 401);
		header('Message: Not authorized');
	}

    public static function accessDenied() : void {
        self::$defaultResponse  = 'notAuthorized';
    }

    protected function responseAccessDenied(string $message = '') : void {
        header("HTTP/1.1 403 Unauthorized");
        header('Status:' . 403);
        header('Message: Not authorized: ' . $message);
    }

	public static function notFound() : void {
		self::$defaultResponse  = 'notFound';
	}

	protected function responseNotFound() : void {
		header("HTTP/1.1 404 Not found");
		header('Status:' . 404);
		header('Message: Not found');
	}

	public static function internalError() : void {
		self::$defaultResponse  = 'internalError';
	}

	protected function responseInternalError() : void {
		header("HTTP/1.1 500 Internal error");
		header('Status:' . 500);
		header('Message: Internal error');
	}


}