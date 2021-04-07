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
use Swift\GraphQl\ContextInterface;
use Swift\GraphQl\Types\NodeTypeInterface;
use Swift\GraphQl\Utils;
use Swift\Kernel\Attributes\DI;
use Swift\Security\User\Controller\UserControllerGraphQl;

/**
 * Class UserType
 * @package Swift\Security\User\Type
 */
#[DI(autowire: false), Type(description: 'Represents user data')]
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
     * @param string|null $password
     */
    public function __construct(
        public ?int $id,
        #[Field] public string $username,
        #[Field(nullable: true)] public ?string $email,
        #[Field] public string $firstname,
        #[Field] public string $lastname,
        #[Field] public DateTime $created,
        #[Field] public DateTime $modified,
        private string|null $password = null,
    ) {
    }

    #[Field( name: 'id', description: 'The user ID' )]
    public function getId(): string {
        return Utils::encodeId('UserType', $this->id);
    }

    /**
     * @inheritDoc
     */
    public static function getNodeResolverClassnameAndMethod( int|string $id, ContextInterface $context ): array {
        return [UserControllerGraphQl::class, 'getUserTypeByNode'];
    }

}