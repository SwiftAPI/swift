<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Types;


use Swift\GraphQl\Attributes\Field;
use Swift\Model\ResultSet;
use Swift\Security\User\Type\UserEdge;
use Swift\Security\User\Type\UserType;

/**
 * Class AbstractConnectionType
 * @package Swift\GraphQl\Types
 */
abstract class AbstractConnectionType implements ConnectionTypeInterface {

    /**
     * UserConnection constructor.
     *
     * @param ResultSet $resultSet
     */
    public function __construct(
        protected ResultSet $resultSet,
    ) {
    }

    #[Field(name: 'totalCount', description: 'Total number of edges in query')]
    public function getTotalCount(): int {
        return $this->resultSet->getTotalCount();
    }

    /**
     * @return PageInfoType
     */
    #[Field( name: 'pageInfo', description: 'Page meta information' )]
    public function getPageInfo(): PageInfoType {
        return PageInfoType::fromModelPageInfo($this->resultSet->getPageInfo());
    }

    /**
     * @TODO Override this method and annotate with #[Field] Attribute
     */
    abstract public function getEdges(): array;

}