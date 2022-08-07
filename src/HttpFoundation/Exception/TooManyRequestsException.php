<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Exception;

use Psr\Http\Message\ResponseInterface;
use Swift\DependencyInjection\Attributes\DI;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\Response;
use Swift\Security\RateLimiter\RateLimit;
use Swift\Security\RateLimiter\RateLimitInterface;
use Throwable;

/**
 * Raised when a user tries to make too many requests or the Rate Limits are exceeded.
 */
#[DI( exclude: true, autowire: false )]
class TooManyRequestsException extends \UnexpectedValueException implements RequestExceptionInterface {
    
    /**
     * BadRequestException constructor.
     *
     * @param \Swift\Security\RateLimiter\RateLimitInterface $rateLimit
     * @param string                                         $message
     * @param int                                            $code
     * @param Throwable|null                                 $previous
     */
    public function __construct(
        protected RateLimitInterface $rateLimit,
        string                       $message = "",
        int                          $code = Response::HTTP_TOO_MANY_REQUESTS,
        Throwable                    $previous = null
    ) {
        $message = $message !== '' ? $message : Response::$reasonPhrases[ $code ];
        
        parent::__construct( $message, $code, $previous );
    }
    
    /**
     * @return \Swift\Security\RateLimiter\RateLimitInterface
     */
    public function getRateLimit(): RateLimitInterface {
        return $this->rateLimit;
    }
    
    public function makeResponse(): ResponseInterface {
        return RateLimit::bindToResponse(
            $this->rateLimit,
            new JsonResponse(
                        [
                            'message'   => $this->getMessage() ?: Response::$reasonPhrases[ Response::HTTP_TOO_MANY_REQUESTS ],
                            'code'      => Response::HTTP_TOO_MANY_REQUESTS,
                            'limit'     => $this->rateLimit->getLimit(),
                            'remaining' => $this->rateLimit->getAvailableTokens(),
                            'reset'     => $this->rateLimit->getResetTime()->getTimestamp(),
                        ],
                status: Response::HTTP_TOO_MANY_REQUESTS
            )
        );
    }
    
}
