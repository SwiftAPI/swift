<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Http\Response;

use JetBrains\PhpStorm\Deprecated;
use Swift\HttpFoundation\Response;

/**
 * Class JSONResponse
 * @package Swift\Http\Response
 */
#[Deprecated(replacement: \Swift\HttpFoundation\JsonResponse::class)]
class JSONResponse extends Response {

    private mixed $response;

    /**
     * JSONResponse constructor.
     *
     * @param array $response
     */
	public function __construct($response = array()) {
	    $this->response = $response;
	}

	public function doOutput(): void {
		http_response_code(200);

		$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

		header('Content-Type: application/json');
        header("Access-Control-Allow-Headers: *");
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

		header('Status:' . 200);

		$encode = json_encode($this->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		echo $encode;
	}
}