<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Honeywell\Service;

use Honeywell\Helper\ConditionWeightHelper;
use Honeywell\Model\Condition;
use Honeywell\Types\ConditionType;
use Honeywell\Types\ConditionTypeEnum;
use Honeywell\Types\ScheduleType;
use stdClass;
use Swift\Kernel\Attributes\Autowire;
use Swift\Model\Entity\Arguments;

/**
 * Class ConditionService
 * @package Honeywell\Service
 */
#[Autowire]
class ConditionService {


    /**
     * ConditionService constructor.
     *
     * @param Condition $condition
     */
    public function __construct(
        private Condition $condition,
    ) {
    }

    /**
     * @param array $state
     * @param Arguments $arguments
     *
     * @return array|null
     */
    public function getConditions( array $state, Arguments $arguments ): ?array {
        return $this->condition->findMany($state, $arguments);
    }

    /**
     * @param string $title
     * @param string $type
     * @param float $temp
     * @param stdClass $rules
     *
     * @return ConditionType
     */
    public function addCondition( string $title, string $type, float $temp, stdClass $rules ): ConditionType {
        $conditionType = new ConditionTypeEnum($type);

        $result = $this->condition->save(array(
            'title' => $title,
            'type' => $conditionType->getValue(),
            'temp' => $temp,
            'weight' => ConditionWeightHelper::weightByType($conditionType),
            'state' => 1,
            'rules' => $rules,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ));

        return new ConditionType(... (array) $result);
    }

    /**
     * @param array $state
     *
     * @return ConditionType
     */
    public function updateCondition( array $state ): ConditionType {
        $result = $this->condition->save($state);

        return new ConditionType(... (array) $state);
    }

}