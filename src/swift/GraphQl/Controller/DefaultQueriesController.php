<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Controller;


use GraphQLRelay\Relay;
use Swift\Controller\AbstractController;
use Swift\GraphQl\Attributes\Query;
use Swift\GraphQl\ContextInterface;
use Swift\GraphQl\TypeRegistryInterface;
use Swift\GraphQl\Types\NodeTypeInterface;
use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\ServiceLocator;
use Swift\Security\User\Type\UserType;
use Swift\Security\User\UserProviderInterface;

/**
 * Class DefaultQueries
 * @package Swift\GraphQl
 */
#[Autowire]
class DefaultQueriesController extends AbstractController {

    /**
     * DefaultQueriesController constructor.
     *
     * @param UserProviderInterface $userProvider
     * @param TypeRegistryInterface $outputTypeRegistry
     * @param ContextInterface $context
     */
    public function __construct(
        private UserProviderInterface $userProvider,
        private TypeRegistryInterface $outputTypeRegistry,
        private ContextInterface $context,
    ) {
    }

    /**
     * Resolve global node field
     *
     * @param string $id
     *
     * @return NodeTypeInterface
     */
    #[Query(description: 'Fetch any object implementing Node Interface by ID')]
    public function Node( string $id ): NodeTypeInterface {
        $idComponents = Relay::fromGlobalId($this->context->getCurrentArguments()['raw']['id']);
        /** @var \GraphQL\Type\Definition\ObjectType $type */
        $type = $this->outputTypeRegistry->getCompiled()->get('UserType');
        /** @var NodeTypeInterface $declaration */
        $declaration = $type->config['declaration']->declaringClass;
        $resolverIdentification = $declaration::getNodeResolverClassnameAndMethod($idComponents['id'], $this->context);
        $instance = (new ServiceLocator())->get($resolverIdentification[0]);
        return $instance?->{$resolverIdentification[1]}($idComponents['id'], $this->context);
    }

}