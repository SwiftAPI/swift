<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Honeywell\Controller;

use Honeywell\Model\Condition;
use Honeywell\Service\ConditionService;
use Honeywell\Types\ConditionType;
use Honeywell\Types\ScheduleType;
use JetBrains\PhpStorm\Pure;
use stdClass;
use Swift\Controller\Controller;
use Swift\GraphQl\Attributes\Argument;
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;
use Swift\GraphQl\Generators\EntityArgumentGenerator;
use Swift\GraphQl\Generators\EntityInputGeneratorNew;
use Swift\GraphQl\Generators\EntityInputGeneratorUpdate;
use Swift\Kernel\TypeSystem\Defaults\Datetime\WeekdaysEnum;
use Swift\Model\Entity\Arguments;
use Swift\Router\HTTPRequest;

/**
 * Class ConditionController
 * @package Honeywell\Controller
 */
class ConditionController extends Controller {

    /**
     * ConditionController constructor.
     *
     * @param HTTPRequest $request
     * @param ConditionService $conditionService
     */
    #[Pure] public function __construct(
        HTTPRequest $request,
        private ConditionService $conditionService,
    ) {
        parent::__construct($request);
    }

    #[Mutation(name: 'addCondition', type: ConditionType::class )]
    public function graphqlAddCondition( #[Argument(type: ConditionType::class, generator: EntityInputGeneratorNew::class)] $condition ): ConditionType {
        return $this->conditionService->addCondition(...$condition);
    }

    #[Mutation(name: 'updateCondition', type: ConditionType::class)]
    public function graphqlUpdateCondition( #[Argument(type: ConditionType::class, generator: EntityInputGeneratorUpdate::class)] array|null $condition ): ConditionType {
        return $this->conditionService->updateCondition($condition);
    }

    #[Query( name: 'conditions', type: ConditionType::class, isList: true)]
    public function graphqlGetConditions(#[Argument(type: Arguments::class, generator: EntityArgumentGenerator::class, generatorArguments: ['entity' => Condition::class])] array|null $filter = null): array {
        $state = $filter['where'] ?? array();
        unset($filter['where']);

        return $this->conditionService->getConditions($state, new Arguments(...$filter));
    }

}