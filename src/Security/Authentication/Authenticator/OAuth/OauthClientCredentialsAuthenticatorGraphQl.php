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
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\Credentials\ClientCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\OathClientCredentialsToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authentication\Token\TokenStoragePoolInterface;
use Swift\Security\User\ClientUser;
use Swift\Security\User\UserProviderInterface;

/**
 * Class OauthClientCredentialsAuthenticatorGraphQl
 * @package Swift\Security\Authentication\Authenticator\OAuth
 */
#[Autowire]
class OauthClientCredentialsAuthenticatorGraphQl implements AuthenticatorInterface {

    /**
     * OAuthClientCredentialsAuthenticator constructor.
     *
     * @param EntityInterface $oauthClientsEntity
     * @param UserProviderInterface $userProvider
     * @param TokenStoragePoolInterface $tokenStoragePool
     */
    public function __construct(
        private EntityInterface $oauthClientsEntity,
        private UserProviderInterface $userProvider,
        private TokenStoragePoolInterface $tokenStoragePool,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function supports( RequestInterface $request ): bool {
        if ($request->getUri()->getPath() !== '/graphql') {
            return false;
        }

        $requestContent = $request->getContent()->get('AuthAccessTokenGet');

        return !empty($requestContent['grantType']) && !empty($requestContent['clientId']) && !empty($requestContent['clientSecret']);
    }

    /**
     * @inheritDoc
     */
    public function authenticate( RequestInterface $request ): PassportInterface {
        $requestContent = $request->getContent()->get('AuthAccessTokenGet');
        $client = $this->oauthClientsEntity->findOne([
            'clientId' => $requestContent['clientId'],
        ]);

        if (!$client) {
            throw new AuthenticationException('Client not found');
        }

        $client = new ClientUser(...$client->toArray());

        return new Passport($client, new ClientCredentials($client->getCredential()));
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new OathClientCredentialsToken($passport->getUser());
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess( RequestInterface $request, /** @var OathClientCredentialsToken */ TokenInterface $token ): ?ResponseInterface {
        // Store refresh token
        $this->tokenStoragePool->setToken($token->getRefreshToken());

        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure( RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        return new JsonResponse(JsonResponse::$reasonPhrases[JsonResponse::HTTP_UNAUTHORIZED], JsonResponse::HTTP_UNAUTHORIZED);
    }
}