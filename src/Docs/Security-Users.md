# Users (& Clients)
Users and clients are treated the same for a large part. But there actually is major difference (see below).

After authentication both will be represented as implementations of ```Swift\Security\User\UserInterface``` for easy usage throughout the application.

#### Users
Users are end users of the application (e.g. customers, etc.)

#### Clients (`Swift\Security\User\ClientUser`)
Clients represent API Consumers.

## Endpoints
Swift comes with a ready to use users endpoint for the following actions:

### For users
- Create user account (REST & GraphQl)
- Login (REST & GraphQl)
- Me (REST & GraphQl) _returns currently authenticated user_
- List Users (REST & GraphQl)
- User by id (REST & GraphQl)
- Forgot password (REST & GraphQl) _generates a 30 minutes valid reset token_
- Reset password (REST & GraphQl) _create new user password_

### For clients
- Get (Oauth) token (REST & GraphQl)
- Refresh (Oauth) token (REST & GraphQl)

## Forgot- and reset password
When a user has forgotten it's password a special token is required to reset it. This available with REST and GraphQl (see example below).

### Forgot password
Forgt password endpoinsts
#### Example: REST
_Request (/users/password/forgot/) POST_
```json
{
    "email": "user@foo.com"
}
```
_Response_
```json
{
    "message": "Successfully requested reset password token. The user has been notified.",
    "code": 200
}
```

#### Example: GraphQl
_Request (/users/password/forgot/) POST_
```graphql
mutation($email: String!) {
    ForgotPassword(email: $email) {
        message
        code
    }
}
```
_Response_
```json
{
  "data": {
    "ForgotPassword": {
      "message": "Successfully requested reset password token. The user has been notified.",
      "code": 200
    }
  }
}
```

### Reset password
As you can see in the example above the resetPasswordToken is not return directly for security reasons. The system does also not do this automatically since it's highly likely you'd want to moderate this message to the user anyway. So you'll need to listen to the Event and notify the user of the token.

#### Example: Notify user of token
See below how this could be achieved. You'd obviously want to do this different, but it gives you an idea.
```php
declare(strict_types=1);

namespace Foo\Listener;

use Swift\Events\Attribute\ListenTo;
use Swift\Security\Authentication\Events\AuthenticationTokenCreatedEvent;
use Swift\Security\Authentication\Token\ResetPasswordToken;

/**
 * Class OnAfterAuthentication
 * @package Foo\Listener
 */
class OnAfterAuthentication {

    /**
     * Assign user roles after token has been created for user
     *
     * @param AuthenticationTokenCreatedEvent $event
     */
    #[ListenTo(event: AuthenticationTokenCreatedEvent::class)]
    public function assignUserRoles( AuthenticationTokenCreatedEvent $event ): void {
        if ($event->getToken() instanceof ResetPasswordToken) {
            mail(
                to: $event->getToken()->getUser()->getEmail(),
                subject: 'Password reset',
                message: sprintf('Hi %s, Hereby your password reset token: %s.', $event->getToken()->getUser()->getFullName(), $event->getToken()->getTokenString())
            );
        }
    }

}
```

#### Example: Rest
_Request (/users/password/reset/) POST_
```json
{
    "resetPasswordToken": "d1c926ba541338e76971c1ded10d147bbd8f1747",
    "newPassword": "henri"
}
```
_Response_
```json
{
    "message": "Successfully reset password",
    "code": 200
}
```

#### Example: GraphQl
_Request_
```graphql
mutation($resetPasswordToken: String!, $newPassword: String!) {
  ResetPassword(resetPasswordInput: {
    resetPasswordToken: $resetPasswordToken,
    newPassword: $newPassword
  }) {
    message
    code
  }
}
```
```json
{
    "data": {
        "ResetPassword": {
            "message": "Successfully reset password.",
            "code": 200
        }
    }
}
```

&larr; [Authentication](https://github.com/HenrivantSant/henri/blob/master/Docs/Authentication.md#authentication) | [GraphQL](https://github.com/HenrivantSant/henri/blob/master/Docs/GraphQL.md#graphql) &rarr;