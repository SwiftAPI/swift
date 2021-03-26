<?php declare(strict_types=1);


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