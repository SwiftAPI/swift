<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Exception;

use JetBrains\PhpStorm\Pure;
use Swift\HttpFoundation\Response;
use Swift\Kernel\Attributes\DI;
use Throwable;

/**
 * Raised when a user sends a malformed request.
 */
#[DI( exclude: true, autowire: false )]
class BadRequestException extends \UnexpectedValueException implements RequestExceptionInterface {

    /**
     * BadRequestException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    #[Pure] public function __construct( $message = "", $code = Response::HTTP_BAD_REQUEST, Throwable $previous = null ) {
        $message = $message !== '' ? $message : Response::$reasonPhrases[$code];

        parent::__construct( $message, $code, $previous );
    }

}
