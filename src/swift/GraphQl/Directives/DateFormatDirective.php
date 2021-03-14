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
use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\Type;
use Swift\GraphQl\ContextInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;

/**
 * Class DateFormatDirective
 * @package Swift\GraphQl\Directives
 */
#[DI(tags: ['graphql.directive']), Autowire]
class DateFormatDirective extends Directive implements DirectiveDefinitionInterface {

    /**
     * DateFormatDirective constructor.
     *
     * @param ContextInterface $context
     */
    public function __construct(
        private ContextInterface $context,
    ) {
        $data = array(
            'name' => 'format',
            'description' => 'Format date',
            'locations' => [DirectiveLocation::FIELD],
            'args' => [
                new FieldArgument([
                    'name' => 'dateformat',
                    'type' => Type::string(),
                    'description' => 'Dateformat to format into. Defaults to "Y-m-d H:i:s"',
                    'defaultValue' => "Y-m-d H:i:s",
                ]),
            ]
        );

        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function execute( mixed $value, array $arguments, FieldNode $fieldNode, string $operationStage ): mixed {
        if ($operationStage === DirectiveInterface::AFTER_VALUE) {
            $value->custom_format = $arguments['dateformat'] ?? 'Y-m-d H:i:s';
        }

        return $value;
    }
}