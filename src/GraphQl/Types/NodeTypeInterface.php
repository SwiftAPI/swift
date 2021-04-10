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
use Swift\GraphQl\Attributes\InterfaceType;
use Swift\GraphQl\ContextInterface;

/**
 * Class NodeTypeInterface
 * @package Swift\GraphQl\Types
 */
#[InterfaceType(name: 'NodeInterface', description: 'An object with an ID')]
interface NodeTypeInterface {

    #[Field(name: 'id', description: 'The id of the object')]
    public function getId(): string;

    /**
     * Return classname and method name for resolver function
     *
     * @param string|int $id
     * @param ContextInterface $context
     *
     * @return array ["resolver classname", "methodName"]
     */
    public static function getNodeResolverClassnameAndMethod( string|int $id, ContextInterface $context ): array;

}