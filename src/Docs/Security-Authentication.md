# Authentication
Authentication represents the process where users or clients confirm they're identity with the application. The authentication process follows a certain number of steps to finally generate a Token. This token represents the access the current user or client has. 

## Authentication process
Authentication is handled by 'Authenticators'. Implementing the `Swift\Security\Authentication\Authenticator\AuthenticatorInterface`. Take a look a REST User Authenticator for example:

A very important note is that the authentication process takes place before actually executing the Request a Controller. By the time a Controller method gets called Authentication has already finished. Authentication therefore NEVER takes place in a Controller. A controller however can still define Authentication Endpoints and return it's reponse. More  on this later.

```php
declare(strict_types=1);

namespace Swift\Security\Authentication\Authenticator\User;


use Swift\HttpFoundation\RequestInterface;
use Swift\HttpFoundation\HeaderBag;
use Swift\HttpFoundation\JsonResponse;
use Swift\HttpFoundation\ResponseInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\EntityInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorEntrypointInterface;
use Swift\Security\Authentication\Authenticator\AuthenticatorInterface;
use Swift\Security\Authentication\Exception\AuthenticationException;
use Swift\Security\Authentication\Passport\Credentials\PasswordCredentials;
use Swift\Security\Authentication\Passport\Passport;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Token\AuthenticatedToken;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\User\UserProviderInterface;

/**
 * Class UserAuthenticator
 * @package Swift\Security\Authentication\Authenticator
 */
#[Autowire]
final class UserAuthenticator implements AuthenticatorInterface, AuthenticatorEntrypointInterface {

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
     * Confirm whether authenticator can authenticate current request
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function supports( RequestInterface $request ): bool {
        /** @var HeaderBag $headers */
        $headers = $request->getHeaders();

        return ($headers->has('php-auth-user') && $headers->has('php-auth-pw'));
    }

    /**
     * Authenticate given request
     *
     * @param RequestInterface $request
     *
     * @return PassportInterface    PassportInterface representing the user
     *
     * @throws AuthenticationException  When authentication fails. In onAuthenticationFailure you will be able to further deal with this and generate a fitting response
     */
    public function authenticate( RequestInterface $request ): PassportInterface {
        /** @var HeaderBag $headers */
        $headers = $request->getHeaders();

        $username = $headers->get('php-auth-user');
        $password = $headers->get('php-auth-pw');

        if (!$user = $this->userProvider->getUserByUsername($username)) {
            throw new AuthenticationException('No user found with given credentials');
        }

        return new Passport($user, new PasswordCredentials( $password ));
    }

    /**
     * Create an authenticated token based on given passport
     *
     * @param PassportInterface $passport
     *
     * @return TokenInterface
     */
    public function createAuthenticatedToken( PassportInterface $passport ): TokenInterface {
        return new AuthenticatedToken(
            user: $passport->getUser(),
            scope: TokenInterface::SCOPE_ACCESS_TOKEN,
            token: null,
            isAuthenticated: true,
        );
    }

    /**
     * Called when successfully authenticated.
     *
     * @param RequestInterface $request
     * @param TokenInterface $token
     *
     * @return ResponseInterface|null   Null will make the request move on. By returning a response this response will be used and the request will not move on
     */
    public function onAuthenticationSuccess( RequestInterface $request, TokenInterface $token ): ?ResponseInterface {
        return null;
    }

    /**
     * Called on authentication failure.
     *
     * @param RequestInterface $request
     * @param AuthenticationException $authenticationException
     *
     * @return ResponseInterface|null   Null will ignore the failure and move on. By returning a response this response will be used and the request will not move on
     */
    public function onAuthenticationFailure( RequestInterface $request, AuthenticationException $authenticationException ): ?ResponseInterface {
        $response = new \stdClass();
        $response->message = $authenticationException->getMessage();
        $response->code = $authenticationException->getCode();
        return new JsonResponse($response, $authenticationException->getCode());
    }
}
```

The authentication process is directed by an Authentication Manager. This manager checks all Authenticators whether the 'support' the given request. Once a Authenticator claims it support the Request, this Authenticator will be executed. No other Authenticator will be searched for nor authenticated against.

### Passport
Once the authenticator claims to support the request (for example credentials have been found in the header) the Authentication Manager will call the authenticate method which is supposed to return a Passport representing the User or Client trying to authentication. This can be the default `Swift\Security\Authentication\Passport\Passport` or any class implementing the `Swift\Security\Authentication\Passport\PassportInterface`. This passport contains the User (implementation of `Swift\Security\User\UserInterface
`) that is found based on the Request and should contain and instance of `Swift\Security\Authentication\Passport\Credentials\CredentialsInterface`. The Passport calls `validateCredentials` on this credentials to validate whether the provided credentials in the Request match the ones belonging the User on the Passport. 

#### Stamps
A Passport can be enriched with Stamps (`Swift\Security\Authentication\Passport\Stamp\StampInterface`). This could for example tell that Authentication is already okay in case of a valid Bearer token. Take a look a AccessTokenAuthenticator::authenticate() for example:
```php
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

    if (!$token->userId && !$token->clientId) {
        throw new AuthenticationException('No user or client related to token');
    }

    if ($token->userId) {
        $user = $this->userProvider->getUserById($token->userId);
    } else {
        $user = $this->oauthClientsEntity->findOne([
            'id' => $token->clientId,
        ]);

        if (!$user) {
            throw new AuthenticationException('Client not found');
        }

        $user = new ClientUser(...(array) $user);
    }

    return new Passport($user, new AccessTokenCredentials($token), array(new PreAuthenticatedStamp($token)));
}
```

#### Attributes
Besides Stamps there's also attributes that can be passed. This no more, and no less than simple meta data that can shipped with the Passport. This could be useful for passing state or redirect data in case of Oauth authentication for example.

### Token (Visa)
After the Passport has been created in the authentication the Authenticator will be passed the Passport to `createAuthenticatedToken( PassportInterface $passport ): TokenInterface` to create a token based on this Passport. This token should implement the `Swift\Security\Authentication\Token\TokenInterface` and plays an important role on the application to provide the authentication user, it's scope, whether the user is authenticated. whether authentication has expired, etc.  

In this scope of Passports and Stamps it would make more sense to name a Token a 'Visa', but since 'Token' is generally accepted. This is the term that will be used. 

## Events
During Authentication several events are dispatched to patch into to append additional data, deny a Request access and more.
```php
/**
 * @param RequestInterface $request
 *
 * @return PassportInterface
 */
public function authenticate( RequestInterface $request ): PassportInterface {
    if ( $authenticator = $this->getAuthenticator( $request ) ) {
        try {
            // Get the passport
            $passport = $authenticator->authenticate( $request );

            // Option for additional passport validation
            $this->eventDispatcher->dispatch( new CheckPassportEvent( $authenticator, $passport ) );

            // Create authenticated token
            $token = $authenticator->createAuthenticatedToken( $passport );
            $token = $this->eventDispatcher->dispatch( new AuthenticationTokenCreatedEvent( $token ) )->getToken();

            // Store the token
            $this->tokenStoragePool->setToken( $token );

            // Finalize request with provided response
            if ( $response = $authenticator->onAuthenticationSuccess( $request, $token ) ) {
                $this->kernel->finalize( $response );
            }

            $this->security->setPassport( $passport );
            $this->security->setUser( $token->getUser() );
            $this->security->setToken( $token );

            $this->eventDispatcher->dispatch( new AuthenticationSuccessEvent( $token, $passport, $request, $authenticator ) );

            return $passport;
        } catch ( AuthenticationException $authenticationException ) {
            if ( $response = $authenticator->onAuthenticationFailure( $request, $authenticationException ) ) {
                $this->kernel->finalize( $response );
            }
        }
    }

    $token    = new Token\NullToken( new NullUser(), TokenInterface::SCOPE_ACCESS_TOKEN, null, false );
    $passport = new Passport( $token->getUser(), new NullCredentials() );
    $this->security->setPassport( $passport );
    $this->security->setUser( $token->getUser() );
    $this->security->setToken( $token );

    return $passport;
}
```

## Entry points
Since authentication occurs before a route is executed a user could potentially authentication against any valid uri. This is not desireable as that might lead to unwanted behaviour, and besides a lot of unclarity for end users.

One possible solution to this is to check the route in supports method in the authenticator and return false if the uri is not as desired. This works!

Another solution is to 'protect' the authenticator by having it implement ``Swift\Security\Authentication\Authenticator\AuthenticatorEntrypointInterface``. This will only allow the authenticator on Entry Point Routes. All Swift's default authenticators implement this execept for the AccessTokenAuthenticator as this is not bound to a specific route.

### Entry point routes
A route can marked as being an Entry Point by added the ENTRY_POINT tag as in the example below.

Also note that authentication has already finished when we get to the controller. We simply just get the user and return it. Also we require the user to be authenticated directly. When is a user is authenticated using a token retrieved by an earlier login this will not be true. This way we make sure we're dealing with a 'fresh' authentication. 
```php
/**
* Rest user authentication endpoint
*
* Authentication already occurs on the security component. So all that needs to be done is return the currently authenticated user
*
* Only a direct login is valid here. Re-authentication or no authentication is not valid. This is already cover through isGranted in the route (validated by the firewall)
*
* @param RouteParameterBag $params
*
* @return JsonResponse
*/
#[Route( method: [RouteMethodEnum::POST], route: '/login/', name: 'security.user.login', isGranted: [AuthorizationTypesEnum::IS_AUTHENTICATED_DIRECTLY], tags: [Route::TAG_ENTRYPOINT] )]
public function login( RouteParameterBag $params ): JsonResponse {
    $data = $this->getCurrentUser()?->serialize();
    $data->created = $data->created->format('Y-m-d H:i:s');
    $data->modified = $data->modified->format('Y-m-d H:i:s');
    $data->token = new \stdClass();
    $data->token->token = $this->getSecurityToken()->getTokenString();
    $data->token->expires = $this->getSecurityToken()->expiresAt()->format('Y-m-d H:i:s');
    
    return new JsonResponse($data);
}
```

## Fetching the Token or User in Service
By injecting the `Swift\Security\Security` class you will be able to fetch the Token, User and Passport in any Service. Note that it's not possible to inject those direct as those are not available yet a Container Compilation time.

## Fetching the Token or User in Controller
A Controller is by default already provided with the Security class through `$this->security`. However there's some handy shortcuts:
- `$this->getSecurityToken()`
- `$this->getCurrentUser()`

There is no shortcut for the Passport as this is mainly relevant during Authentication before a token has been granted.

&larr; [Logging](https://github.com/HenrivantSant/henri/blob/master/Docs/Logging.md#logging) | [Users](https://github.com/HenrivantSant/henri/blob/master/Docs/Users.md#users) &rarr;