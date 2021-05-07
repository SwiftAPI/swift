<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Authenticator\OAuth;


use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\RequestInterface;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\EntityInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorEntrypointInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\Credentials\RefreshTokenCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\OauthAccessToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authentication\Token\TokenStorageInterface;
use Swift\Security\Authentication\Token\TokenStoragePoolInterface;
use Swift\Security\User\ClientUser;
use Swift\Security\User\UserProviderInterface;

/**
 * Class OAuthRefreshTokenAuthenticator
 * @package Swift\Security\Authentication\Authenticator\OAuth
 */
#[Autowire]
class OAuthRefreshTokenAuthenticator implements AuthenticatorInterface, AuthenticatorEntrypointInterface {

    /**
     * OAuthRefreshTokenAuthenticator constructor.
     *
     * @param EntityInterface $oauthClientsEntity
     * @param UserProviderInterface $userProvider
     * @param TokenStorageInterface $databaseTokenStorage
     * @param TokenStoragePoolInterface $tokenStoragePool
     */
    public function __construct(
        private EntityInterface $oauthClientsEntity,
        private UserProviderInterface $userProvider,
        private TokenStorageInterface $databaseTokenStorage,
        private TokenStoragePoolInterface $tokenStoragePool,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function supports( RequestInterface $request ): bool {
        $requestContent = $request->getContent();

        return !empty($requestContent->get('grant_type')) &&
               ($requestContent->get('grant_type') === 'refresh_token') &&
               !empty($requestContent->get('refresh_token'));
    }

    /**
     * @inheritDoc
     */
    public function authenticate( RequestInterface $request ): PassportInterface {
        $requestContent = $request->getContent();

        $token = $this->databaseTokenStorage->findOne([
            'accessToken' => $requestContent->get('refresh_token'),
            'scope' => 'SCOPE_REFRESH_TOKEN',
        ]);

        if (!$token) {
            throw new AuthenticationException('Invalid token');
        }

        $client = $this->oauthClientsEntity->findOne([
            'id' => $token->clientId,
        ]);

        if (!$client) {
            throw new AuthenticationException('Invalid token');
        }

        $client = new ClientUser(...$client->toArray());

        return new Passport($client, new RefreshTokenCredentials($token));
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new OauthAccessToken($passport->getUser());
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess( RequestInterface $request, /** @var OauthAccessToken */ TokenInterface $token ): ?ResponseInterface {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        return new JsonResponse(JsonResponse::$reasonPhrases[JsonResponse::HTTP_UNAUTHORIZED], JsonResponse::HTTP_UNAUTHORIZED);
    }

}