<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\User\Type;


use Swift\GraphQl\Attributes\Field;
use Swift\GraphQl\Attributes\Type;
use Swift\GraphQl\Types\AbstractConnectionType;

/**
 * Class UserConnection
 * @package Swift\Security\User\Type
 */
#[Type]
class UserConnection extends AbstractConnectionType {

    /**
     * @return UserEdge[]
     */
    #[Field(name: 'edges', type: UserEdge::class, isList: true)]
    public function getEdges(): array {
        $edges = array();
        foreach ($this->resultSet as $user) {
            unset($user->password);
            $edges[] = new UserEdge($user->id, new UserType(...$user->toArray()));
        }

        return $edges;
    }

}