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
use Swift\GraphQl\Types\AbstractEdgeType;
use Swift\GraphQl\Types\NodeTypeInterface;

/**
 * Class UserEdge
 * @package Swift\Security\User\Type
 */
#[Type]
class UserEdge extends AbstractEdgeType {

    /**
     * @return NodeTypeInterface
     */
    #[Field( name: 'node', type: UserType::class, description: 'The item at the end of the edge' )]
    public function getNode(): NodeTypeInterface {
        return $this->node;
    }

}