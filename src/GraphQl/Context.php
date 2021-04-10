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
use JetBrains\PhpStorm\ArrayShape;
use Swift\GraphQl\Directives\Directive;
use GraphQL\Type\Definition\ResolveInfo;
use Swift\GraphQl\Directives\DirectiveInterface;

/**
 * Class Context
 * @package Swift\GraphQl
 */
class Context implements ContextInterface {

    private ResolveInfo $info;
    private array $currentArguments;

    /**
     * @inheritDoc
     */
    public function getInfo(): ResolveInfo {
        return $this->info;
    }

    public function setInfo( ResolveInfo $info ): void {
        $this->info = $info;
    }

    /**
     * Get directives
     *
     * @return DirectiveInterface[]
     */
    public function getDirectives(): array {
        $directives = array();

        if (!empty($this->getInfo()->fieldNodes[0]->directives)) {
            foreach ($this->getInfo()->fieldNodes[0]->directives as $directive) {
                $directives[$directive->name->value] = new Directive(
                    $directive,
                    $this->getInfo()->schema->getDirective($directive->name->value),
                    $this->getCurrentField()
                );
            }
        }

        return $directives;
    }

    /**
     * @inheritDoc
     */
    public function getDirectiveByName( string $name ): ?DirectiveInterface {
        $directives = $this->getDirectives();

        return $directives[ $name ] ?? null;
    }

    public function getCurrentField(): FieldNode {
        return $this->getInfo()->fieldNodes[0];
    }

    public function setCurrentArguments( array $arguments ): void {
        $this->currentArguments = $arguments;
    }

    #[ArrayShape(shape: ['raw' => array(), 'parsed' => array()])]
    public function getCurrentArguments(): array {
        return $this->currentArguments;
    }

}