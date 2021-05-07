<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;

/**
 * Class CorsResponse
 * @package Swift\HttpFoundation\Response
 */
final class CorsResponse extends Response {

    protected function doOutput(): void {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }

    public function sendOutput(): void {
        $this->doOutput();
    }
}