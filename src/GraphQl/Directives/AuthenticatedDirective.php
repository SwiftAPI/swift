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
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Security\Authorization\AuthorizationCheckerInterface;
use Swift\Security\Authorization\AuthorizationTypesEnum;

/**
 * Class AuthenticatedDirective
 * @package Swift\GraphQl\Directives
 */
#[DI(tags: ['graphql.directive']), Autowire]
class AuthenticatedDirective extends Directive implements DirectiveDefinitionInterface {

    /**
     * AuthenticatedDirective constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
        $data = array(
            'name' => 'authenticated',
            'description' => 'Disallow if not properly authenticated',
            'locations' => [DirectiveLocation::QUERY, DirectiveLocation::MUTATION, DirectiveLocation::FIELD],
            'args' => [
                new FieldArgument([
                    'name' => 'role',
                    'type' => Type::listOf(Type::string()),
                    'description' => 'Roles to authorize against. Defaults to IS_AUTHENTICATED',
                    'defaultValue' => AuthorizationTypesEnum::IS_AUTHENTICATED,
                ]),
            ]
        );

        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function execute( mixed $value, array $arguments, FieldNode $fieldNode, string $operationStage ): mixed {
        if ($operationStage === DirectiveInterface::BEFORE_VALUE) {
            if (!empty($arguments['role'])) {
                foreach ($arguments['role'] as $argument) {
                    if (!AuthorizationTypesEnum::isValid($argument)) {
                        new AuthorizationTypesEnum($argument);
                    }
                }
            }

            $this->authorizationChecker->denyUnlessGranted($arguments['role'] ?? array() );
        }

        return $value;
    }
}