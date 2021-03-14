<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Type\Definition\ResolveInfo;
use Swift\GraphQl\Directives\DirectiveInterface;

/**
 * Interface ContextInterface
 * @package Swift\GraphQl
 */
interface ContextInterface {

    /**
     * @return ResolveInfo
     */
    public function getInfo(): ResolveInfo;

    /**
     * Get directives
     *
     * @return DirectiveInterface[]
     */
    public function getDirectives(): array;

    /**
     * @param string $name
     *
     * @return DirectiveInterface|null
     */
    public function getDirectiveByName( string $name ): ?DirectiveInterface;

    /**
     * @return FieldNode
     */
    public function getCurrentField(): FieldNode;

}