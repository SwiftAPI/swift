<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Directives;

use GraphQL\Language\AST\FieldNode;
use Swift\Kernel\Attributes\DI;

/**
 * Interface DirectiveDefinitionInterface
 * @package Swift\GraphQl\Directives
 */
#[DI(tags: ['graphql.directive'])]
interface DirectiveDefinitionInterface {

    /**
     * Execute/validate definition
     *
     * @param mixed $value
     * @param array $arguments
     * @param FieldNode $fieldNode
     * @param string $operationStage Execute is called twice. Once before value initialization and once after. The stage is passed as an the second argument
     *                               Usually only one desired. Directives used for formatting the value are useful in the first case. The decide whether a
     *                               field should be included, BEFORE_VALUE is more suited.
     *
     * @return mixed the value
     */
    public function execute( mixed $value, array $arguments, FieldNode $fieldNode, string $operationStage ): mixed;

}