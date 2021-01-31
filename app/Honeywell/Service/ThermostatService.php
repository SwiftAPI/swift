<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Honeywell\Service;

use Honeywell\Helper\ScheduleHelper;
use Honeywell\Model\Condition;
use Honeywell\Model\Thermostat;
use Honeywell\Types\ScheduleType;
use Honeywell\Types\ScheduleTypesEnum;
use Honeywell\Types\ThermostatState;
use JetBrains\PhpStorm\Deprecated;
use stdClass;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class ThermostatService
 * @package Honeywell\Service
 */
#[Autowire]
class ThermostatService {

    /**
     * ThermostatService constructor.
     *
     * @param Thermostat $thermostat
     * @param Condition $schedule
     * @param ConditionService $scheduleService
     * @param ScheduleHelper $scheduleHelper
     */
    public function __construct(
        private Thermostat $thermostat,
        private Condition $schedule,
        private ConditionService $scheduleService,
        private ScheduleHelper $scheduleHelper,
    ) {
    }

    public function getThermostatById( int $id ): ?stdClass {
        return $this->thermostat->findOne(['id' => $id]);
    }

    /**
     * @param int $id
     *
     * @return ThermostatState|null
     */
    public function getThermostatStateById( int $id ): ?ThermostatState {
        $thermostat = $this->thermostat->findOne(['id' => $id]);

        if (!$thermostat) {
            return null;
        }

        $state = (array) $thermostat->state;
        $state['id'] = $thermostat->id;

        return new ThermostatState(...$state);
    }

    /**
     * @param float $temp
     *
     * @return ScheduleType
     */
    #[Deprecated]
    public function setOverrideScheduleTillNext( float $temp ): ScheduleType {
        $currentSchedule = $this->scheduleService->getCurrentRunningSchedule();

        $params = array('temp' => $temp);

        if ($currentSchedule?->type === ScheduleTypesEnum::TILL_NEXT) {
            // Is already running a till_next schedule
            // Add the id so it will be updated
            $params['id'] = $currentSchedule?->id;
        } else {
            // Append default config for till_next schedule type
            $params = array_merge($params, [
                'title' => 'Until next',
                'deviceID' => 1,
                'start' => date('Y-m-d H:i:s'),
                'end' => date('Y-m-d H:i:s'),
                'geofenced' => false,
                'geoAwayTemp' => 16,
                'geoRadius' => 3000,
                'type' => ScheduleTypesEnum::TILL_NEXT,
                'params' => $this->scheduleHelper->populateParams(array(
                    'type'      => ScheduleTypesEnum::TILL_NEXT,
                    'timing'    => array(
                        'startTime' => date('H:i' ),
                        'endTime'   => date('H:i', strtotime($currentSchedule?->params->endTime)),
                    ),
                )),
                'state' => 1,
                'created' => date('Y-m-d'),
            ]);
        }

        $result = $this->schedule->save($params);
        $result = (array) $result;
        return new ScheduleType(...$result);
    }
}