<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Router\Model;

use Exception;
use stdClass;
use Swift\Kernel\Attributes\Autowire;
use Swift\Router\Model\Entity\LogRequest;

/**
 * Class Request
 * @package Swift\Router\Model
 */
#[Autowire]
class Request {

    /**
     * Request constructor.
     *
     * @param LogRequest $entityLogRequest
     */
	public function __construct(
		private LogRequest $entityLogRequest
	) {
	}

    /**
     * Method to log a request
     *
     * @param string $ip
     * @param string $origin
     * @param string $time
     * @param string $method
     * @param array $headers
     * @param null $body
     * @param int $code
     *
     * @throws Exception
     */
	public function logRequest(string $ip, string $origin, string $time, string $method, array $headers, $body = null, int $code = 0): void {
		$request    = new stdClass();
		$request->ip        = $ip;
		$request->origin    = $origin;
		$request->time      = $time;
		$request->method    = $method;
		$request->headers   = (object) $headers;
		$request->body      = $body;
		$request->code      = $code;

		if (!$this->entityLogRequest->save($request)) {
			throw new Exception('Error on logging request', 500);
		}
	}


}