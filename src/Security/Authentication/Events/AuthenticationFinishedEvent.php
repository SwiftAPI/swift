<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Events;


use Psr\Http\Message\RequestInterface;
use Swift\Events\AbstractEvent;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\TokenInterface;

/**
 * Class AuthenticationFinishedEvent
 * @package Swift\Security\Authentication\Events
 */
class AuthenticationFinishedEvent extends AbstractEvent {

    protected static string $eventDescription = 'Authentication process is completed';
    protected static string $eventLongDescription = '';


    /**
     * AuthenticationSuccessEvent constructor.
     *
     * @param TokenInterface $token
     * @param PassportInterface $passport
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly TokenInterface    $token,
        private readonly PassportInterface $passport,
        private readonly RequestInterface  $request,
    ) {
    }

    /**
     * @return TokenInterface
     */
    public function getToken(): TokenInterface {
        return $this->token;
    }

    /**
     * @return PassportInterface
     */
    public function getPassport(): PassportInterface {
        return $this->passport;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
        return $this->request;
    }

}