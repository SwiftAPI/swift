<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Directives;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\FieldNode;
use Swift\Kernel\Attributes\DI;

/**
 * Class Directive
 * @package Swift\GraphQl\Directives
 */
#[DI(autowire: false)]
class Directive implements DirectiveInterface {

    /**
     * Directive constructor.
     *
     * @param DirectiveNode $directiveNode
     * @param DirectiveDefinitionInterface|\GraphQL\Type\Definition\Directive $directive
     * @param FieldNode $fieldNode
     */
    public function __construct(
        private DirectiveNode $directiveNode,
        private DirectiveDefinitionInterface|\GraphQL\Type\Definition\Directive $directive,
        private FieldNode $fieldNode,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(mixed $value, string $operationStage): mixed {
        if ($this->directive instanceof DirectiveDefinitionInterface) {
            $value = $this->directive->execute($value, $this->getArguments(), $this->getField(), $operationStage);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getArguments(): array {
        $arguments = array();

        foreach ($this->directiveNode->arguments->getIterator() as $argument) {
            if (property_exists($argument->value, 'values')) {
                $arguments[$argument->name->value] = array();
                foreach ($argument->value->values->getIterator() as $value) {
                    $arguments[$argument->name->value][] = $value->value;
                }
            }
            if (property_exists($argument->value, 'value')) {
                $arguments[$argument->name->value] = $argument->value->value;
            }
        }

        return $arguments;
    }

    public function getField(): FieldNode {
        return $this->fieldNode;
    }

}