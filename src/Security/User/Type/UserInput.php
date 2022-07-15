<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Type;

use Swift\DependencyInjection\Attributes\DI;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\InputType;

/**
 * Class UserInputType
 * @package Swift\Security\User\Type
 */
#[DI(autowire: false), InputType(description: 'User creation input')]
class UserInput {

    /**
     * UserType constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     */
    public function __construct(
        #[Field] public string $username,
        #[Field] public string $password,
        #[Field] public string $email,
        #[Field] public string $firstname,
        #[Field] public string $lastname,
    ) {
    }
}