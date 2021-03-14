<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Type;

use DateTime;
use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\Types\NodeTypeInterface;
use Swift\Kernel\Attributes\DI;

/**
 * Class UserType
 * @package Swift\Security\User\Type
 */
#[DI(autowire: false), Type]
class UserType implements NodeTypeInterface {

    /**
     * UserType constructor.
     *
     * @param int|null $id
     * @param string $username
     * @param string|null $email
     * @param string $firstname
     * @param string $lastname
     * @param DateTime $created
     * @param DateTime $modified
     */
    public function __construct(
        #[Field] public ?int $id,
        #[Field] public string $username,
        #[Field(nullable: true)] public ?string $email,
        #[Field] public string $firstname,
        #[Field] public string $lastname,
        #[Field(type: \Swift\GraphQl\Types\Type::DATETIME)] public DateTime $created,
        #[Field(type: \Swift\GraphQl\Types\Type::DATETIME)] public DateTime $modified,
    ) {
    }

    #[Field( name: 'id' )]
    public function getId(): int {
        return $this->id;
    }
}