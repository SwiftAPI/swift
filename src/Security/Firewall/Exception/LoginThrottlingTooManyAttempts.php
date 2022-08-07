<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Firewall\Exception;


use Psr\Http\Message\ResponseInterface;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\Response;
use Swift\Security\RateLimiter\RateLimit;
use Swift\Security\RateLimiter\RateLimitInterface;
use Throwable;

class LoginThrottlingTooManyAttempts extends \Swift\HttpFoundation\Exception\TooManyRequestsException {
    
    /**
     * BadRequestException constructor.
     *
     * @param \Swift\Security\RateLimiter\RateLimitInterface $rateLimit
     * @param string                                         $message
     * @param int                                            $code
     * @param Throwable|null                                 $previous
     */
    public function __construct(
        RateLimitInterface $rateLimit,
        string             $message = "",
        int                $code = Response::HTTP_TOO_MANY_REQUESTS,
        Throwable          $previous = null
    ) {
        $interval = ( new \DateTimeImmutable() )->diff( $rateLimit->getResetTime() );
        $message  = $message !== '' ? $message : 'Too many attempts. Try again in ' . $interval->i . ' minutes.';
        
        parent::__construct( $rateLimit, $message, $code, $previous );
    }
    
    public function makeResponse(): ResponseInterface {
        return RateLimit::bindToResponse(
            $this->rateLimit,
            new JsonResponse(
                        [
                            'message'   => $this->getMessage(),
                            'code'      => Response::HTTP_TOO_MANY_REQUESTS,
                            'reset'     => $this->rateLimit->getResetTime()->getTimestamp(),
                        ],
                status: Response::HTTP_TOO_MANY_REQUESTS
            )
        );
    }
    
}