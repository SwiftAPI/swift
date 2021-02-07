<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Authenticator;


use Psr\Http\Message\RequestInterface;
use Swift\HttpFoundation\HeaderBag;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\Response;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\EntityInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Exception\InvalidCredentialsException;
use Swift\Security\Authentication\Passport\Credentials\AccessTokenCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Passport\Stamp\PreAuthenticatedStamp;
use Swift\Security\Authentication\Token\PreAuthenticatedToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\AnonymousUser;
use Swift\Security\User\UserProviderInterface;

/**
 * Class AccessTokenAuthenticator
 * @package Swift\Security\Authentication\Authenticator
 */
#[Autowire]
final class AccessTokenAuthenticator implements AuthenticatorInterface {

    /**
     * AccessTokenAuthenticator constructor.
     *
     * @param EntityInterface $accessTokenEntity
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        private EntityInterface $accessTokenEntity,
        private UserProviderInterface $userProvider,
    ) {
    }

    /**
     * Support Bearer tokens
     *
     * @inheritDoc
     */
    public function supports( RequestInterface $request ): bool {
        /** @var HeaderBag $headers */
        $headers = $request->getHeaders();

        return ($headers->has('authorization') && str_starts_with($headers->get('authorization'), 'Bearer '));
    }

    /**
     * @inheritDoc
     */
    public function authenticate( RequestInterface $request ): PassportInterface {
        /** @var HeaderBag $headers */
        $headers = $request->getHeaders();
        $accessToken = str_replace('Bearer ', '', $headers->get('authorization'));

        if (!$token = $this->accessTokenEntity->findOne(array('accessToken' => $accessToken))) {
            throw new InvalidCredentialsException('No valid token found', Response::HTTP_UNAUTHORIZED);
        }

        $user = $token->userId ?
            $this->userProvider->getUserById($token->userId) :
            new AnonymousUser();

        return new Passport($user, new AccessTokenCredentials($user, $token), array(new PreAuthenticatedStamp($token)));
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new PreAuthenticatedToken(
            user: $passport->getUser(),
            token: $passport->getStamp(PreAuthenticatedStamp::class)->getToken(),
        );
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess( RequestInterface $request, TokenInterface $token ): ?ResponseInterface {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        $response = new \stdClass();
        $response->message = $authenticationException->getMessage();
        $response->code = $authenticationException->getCode();
        return new JsonResponse($response, $authenticationException->getCode());
    }
}