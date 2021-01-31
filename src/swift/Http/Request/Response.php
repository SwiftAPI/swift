<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Http\Request;


use Unirest\Response as UnirestResponse;
use stdClass;

/**
 * Class Response
 * @package Swift\Http\Request
 */
class Response {

    /**
     * @var int $code response code of the cURL request
     */
    public $code;

    /**
     * @var stdClass $raw_body the raw body of the cURL response
     */
    public $raw_body;

    /**
     * @var stdClass $body parsed body of the response
     */
    public $body;

    /**
     * @var array $headers reponse headers
     */
    public $headers;

    /**
     * Response constructor.
     *
     * @param UnirestResponse|null $response
     */
    public function __construct(UnirestResponse $response = null) {
        $this->code = $response->code;
        $this->raw_body = $response->raw_body;
        $this->body = $response->body;
        $this->headers = $response->headers;
    }

}