<?php declare(strict_types=1);
/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Http\Request\Exceptions;


use Swift\Http\Request\Response;
use RuntimeException;
use Throwable;

class RequestFailed extends RuntimeException {

    /**
     * @var Response $reponse
     */
    private $reponse;
    
    public function __construct( string $message = "", Response $response = null, int $code = 0, Throwable $previous = null ) {
        $this->reponse = $response;
        
        parent::__construct( $message, $code, $previous );
    }

    /**
     * @return Response
     */
    public function getReponse(): Response {
        return $this->reponse;
    }

}