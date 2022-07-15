# Authorization
Authorization where is determined whether a user (clients are treated as users too) is allowed access to a certain resource/functionality.

Most commonly this happens based on a User Role or Authentication Status (Authenticated/Not Authenticated). Also it's quite common to validate whether a User or a Client is requesting a resource.


## Voters
To determine whether access is granted system of Voters is used. Voters should implement the VoterInterface. All voters are automatically registered in the AccessDecisionManager. The AccessDecisionManager will ask all voters to vote on the provided subject. A voter can return three possible answers:
- ACCESS_GRANTED
- ACCESS_DENIED
- ACCESS_ABSTAIN

Abstain is relevant when the voter has no possible answer. E.g. the Authenticated Voter has no clue when there's asked for a vote on a User Role. In this case the voter would abstain from voting.

The component comes with default Voters on User Roles and User Authentication. No need to write custom Voters on this subject if you're adding major extended functionality in that area.

```php
declare(strict_types=1);

namespace Swift\Security\Authorization\Voter;

use Swift\Kernel\Attributes\DI;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\DiTags;

/**
 * Class VoterInterface
 * @package Swift\Security\Authorization\Voter
 */
#[DI(tags: [DiTags::SECURITY_AUTHORIZATION_VOTER])]
interface VoterInterface {

    public const ACCESS_GRANTED = 'ACCESS_GRANTED';
    public const ACCESS_DENIED = 'ACCESS_DENIED';
    public const ACCESS_ABSTAIN = 'ACCESS_ABSTAIN';

    /**
     * Vote
     *
     * @param TokenInterface $token
     * @param mixed $subject
     * @param array $attributes
     *
     * @return string ACCESS_GRANTED || ACCESS_DENIED || ACCESS_ABSTAIN
     */
    public function vote( TokenInterface $token, mixed $subject, array $attributes ): string;

}
```

### User Role Voter
User Role Voter confirms whether a user has a certain role. Do not use Swift\Security\User\UserInterface::getRoles() to determine whether the user has a certain role as this only return the direct assigned roles. Roles can be related with each other or have a certain hierarchy. There are some default options:
```php
declare(strict_types=1);

namespace Swift\Security\Authorization;

/**
 * Class AuthorizationRole
 * @package Swift\Security\Authorization
 */
enum AuthorizationRole {

    // Main roles
    case ROLE_GUEST = 'ROLE_GUEST';
    case ROLE_USER = 'ROLE_USER';
    case ROLE_CLIENT = 'ROLE_CLIENT';
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    // Sub roles
    case ROLE_USERS_LIST = 'ROLE_USERS_LIST';

}
```

### Authenticated Voter
The Authenticated voter determines whether the currently a user or client has authenticated, but also how it authenticated. This are the options:
```php
declare(strict_types=1);

namespace Swift\Security\Authorization;


use Swift\Kernel\TypeSystem\Enum;

/**
 * Class AuthorizationType
 * @package Swift\Security\Authorization
 */
class AuthorizationType extends Enum {

    public const IS_AUTHENTICATED = 'IS_AUTHENTICATED';
    public const IS_AUTHENTICATED_DIRECTLY = 'IS_AUTHENTICATED_DIRECTLY';
    public const IS_AUTHENTICATED_ANONYMOUSLY = 'IS_AUTHENTICATED_ANONYMOUSLY';
    public const IS_AUTHENTICATED_TOKEN = 'IS_AUTHENTICATED_TOKEN';
    public const PUBLIC_ACCESS = 'PUBLIC_ACCESS';

}
```

### Custom Voter
To create a custom voter simply implement the ``Swift\Security\Authorization\Voter\VoterInterface``. This Interface is pre-tagged and will automatically register in the AccessDecisionManager. 

Note: Make sure to return ACCESS_ABSTAIN is no vote could be made!

## Strategies
By default there's four possible strategies on voting.
- ``Swift\Security\Authorization\Strategy\AffirmativeDecisionStrategy``
  Grants access if any voter returns an affirmative response
- ``Swift\Security\Authorization\Strategy\ConsensusDecisionStrategy``  
  Grants access if there is consensus of granted against denied responses.  
  Consensus means majority-rule (ignoring abstains) rather than unanimous agreement (ignoring abstains).
- ``Swift\Security\Authorization\Strategy\PriorityDecisionStrategy``  
  Grant or deny access depending on the first voter that does not abstain.  
  The priority of voters can be used to overrule a decision.
- ``Swift\Security\Authorization\Strategy\UnanimousDecisionStrategy``
  Grants access if only grant (ignoring abstain) votes were received.
  
What if all voters abstain from voting? By default access is denied when all voters abstain from voting. The can be overruled in the configuration.

The default strategy is ``Swift\Security\Authorization\Strategy\AffirmativeDecisionStrategy``. This can be overruled on the configuration.

### Custom strategy
Easily create your own strategy by implementing the ``Swift\Security\Authorization\Strategy\DecisionStrategyInterface``. The Interface is pre-tagged and will automatically register the Strategy. To use it as default set it as default in the Security Configuration.

## Roles
Roles are used to represent the users authenticity and what the user is allowed to do. Roles can be defined in the configuration as below. The roles in de example below are already present by default. Custom Roles can be added. A role will automatically also have all it's child roles. So ROLE_CLIENT will also have ROLE_USERS_LIST. To take it a step further, ROLE_SUPER_ADMIN will also have ROLE_ADMIN and therefore also ROLE_USERS_LIST. See how this works now?

### Assign role to authenticated user
Now it's highly possible you'd want to assign a user more rights or validate the given rights of a user once it authenticates.

The proper way to do this is to listen to the authentication events and add/remove the appropriate roles.

Most appropriate events:
- ``Swift\Security\Authentication\Events\AuthenticationTokenCreatedEvent``  
  Token has been created, user authentication is not validated yet
- ``Swift\Security\Authentication\Events\AuthenticationSuccessEvent``  
  Token has been created, authentication is successful. This usually makes the most sense.

_Example_
```php
/**
 * Listen to AuthenticationSuccessEvent callback and add/remove roles accordingly
 * 
 * @param AuthenticationSuccessEvent $event
 */
#[ListenTo(event: AuthenticationSuccessEvent::class)]
public function onAuthenticationFinished( AuthenticationSuccessEvent $event ): void {
    // Example where user with id 3 should have an extra role
    if ($event->getToken()->getUser()->getId() === 3) {
        $event->getToken()->getUser()->getRoles()->set('ROLE_USER_DEMO');
    }
    
    // Example where user with id 4 should not have given role
    if ($event->getToken()->getUser()->getId() === 4) {
        $event->getToken()->getUser()->getRoles()->remove('ROLE_USER_DEMO');
    }
}
```

## Configuration
Security configuration happens through separate configuration file /etc/security.yaml. 
```yaml
enable_firewalls: true

firewalls:
  main:
    # limit login attempts, defaults to 5 per minute. Set to 0 to disable throttling
    login_throttling:
      max_attempts: 5

role_hierarchy:
  ROLE_GUEST:
  ROLE_USER:
  ROLE_CLIENT: ['ROLE_USERS_LIST']
  ROLE_ADMIN: ['ROLE_USERS_LIST']
  ROLE_SUPER_ADMIN: ['ROLE_ADMIN']

access_decision_manager:
  strategy: Swift\Security\Authorization\Strategy\AffirmativeDecisionStrategy
  allow_if_all_abstain: false

access_control:
```

## Usage
Authorization validation is presented through a simple interface called the AuthorizationChecker. It comes with two methods.

Inject it with ``Swift\Security\Authorization\AuthorizationCheckerInterface $authorizationChecker``

```php
declare(strict_types=1);

namespace Swift\Security\Authorization;

use Swift\HttpFoundation\Exception\AccessDeniedException;

/**
 * Interface AuthorizationCheckerInterface
 * @package Swift\Security\Authorization
 */
interface AuthorizationCheckerInterface {

    /**
     * Validate whether given subject should be available
     *
     * @param array $attributes
     * @param mixed $subject
     * @param string|null $strategy
     *
     * @return bool
     */
    public function isGranted( array $attributes, mixed $subject = null, string $strategy = null ): bool;

    /**
     * Validate whether given subject should be available
     * Throw exception when not granted
     *
     * @param array $attributes
     * @param mixed $subject
     * @param string|null $strategy
     *
     * @return void
     *
     * @throws AccessDeniedException
     */
    public function denyUnlessGranted( array $attributes, mixed $subject = null, string $strategy = null ): void;

}
```

#### How to use
```php
// Returns a boolean (true on granted, false on not granted)
$this->authorizationChecker->isGranted([AuthorizationRole::ROLE_CLIENT]);

// Throws Swift\HttpFoundation\Exception\AccessDeniedException when not granted.
$this->authorizationChecker->denyUnlessGranted([AuthorizationRole::ROLE_CLIENT]);
```

## Controller shortcuts
Controllers are equipped with some handy shortcuts. In a Controller you can directly call ``$this->denyAccessUnlessGranted()``.

However even more useful for REST endpoints is the isGranted parameter on the Route Attribute. Below an example of the '/users/me/' endpoint.
```php
/**
 * Return currently authenticated user. For this it is required that a user is authenticated
 *
 * @param RouteParameter[] $params
 *
 * @return JsonResponse
 */
#[Route( method: RouteMethod::GET, route: '/me/', name: 'security.users.me', isGranted: [AuthorizationType::IS_AUTHENTICATED] )]
public function me( array $params ): JsonResponse {
    $data = $this->getCurrentUser()->serialize();
    unset($data->password);

    return new JsonResponse($data);
}
```

&larr; [Logging](https://github.com/HenrivantSant/henri/blob/master/Docs/Logging.md#logging) | [Users](https://github.com/HenrivantSant/henri/blob/master/Docs/Users.md#users) &rarr;