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
 * Interface DirectiveInterface
 * @package Swift\GraphQl\Directives
 */
interface DirectiveInterface {

    public const BEFORE_VALUE  = 'before_value';
    public const AFTER_VALUE  = 'after_value';

    /**
     * Execute directive
     *
     * @param mixed $value
     * @param string $operationStage Execute is called twice. Once before value initialization and once after. The stage is passed as an the second argument
     *                               Usually only one desired. Directives used for formatting the value are useful in the first case. The decide whether a
     *                               field should be included, BEFORE_VALUE is more suited.
     *
     * @return  mixed the value
     */
    public function execute(mixed $value, string $operationStage): mixed;

    /**
     * Get directive arguments
     *
     * @return array
     */
    public function getArguments(): array;

    /**
     * Get belonging field
     *
     * @return FieldNode
     */
    public function getField(): FieldNode;

}