<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\EventSubscriber;

use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Events\Attribute\ListenTo;
use Swift\Events\EventListenerInterface;
use Swift\Security\Authentication\Events\AuthenticationTokenCreatedEvent;

/**
 * Class OnAfterAuthentication
 * @package Swift\Security\User\EventSubscriber
 */
#[Autowire]
class OnAfterAuthentication implements EventListenerInterface {

    /**
     * Assign user roles after token has been created for user
     *
     * @param AuthenticationTokenCreatedEvent $event
     */
    #[ListenTo(event: AuthenticationTokenCreatedEvent::class)]
    public function assignUserRoles( AuthenticationTokenCreatedEvent $event ): void {

    }

}