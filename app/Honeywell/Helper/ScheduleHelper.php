<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Honeywell\Helper;


use Honeywell\Types\ScheduleTypesEnum;
use stdClass;

/**
 * Class ScheduleHelper
 * @package Honeywell\Helper
 */
class ScheduleHelper {

    /**
     * Method to populate params
     *
     * @param array $data
     *
     * @return stdClass
     */
    public function populateParams(array $data) : stdClass {
        $params = new stdClass();

        if ($data['type'] === ScheduleTypesEnum::RECURRING || $data['type'] === ScheduleTypesEnum::DEFAULT) {
            $params->days       = $data['days'];
        }

        if ($data['type'] === ScheduleTypesEnum::RECURRING || $data['type'] === ScheduleTypesEnum::DEFAULT || $data['type'] === ScheduleTypesEnum::ONCE || $data['type'] === ScheduleTypesEnum::TILL_NEXT) {
            $params->startTime  = date('H:i', strtotime($data['timing']['startTime']));
            $params->endTime    = date('H:i', strtotime($data['timing']['endTime']));
        }

        if ($data['type'] === ScheduleTypesEnum::ONCE || $data['type'] === ScheduleTypesEnum::TILL_NEXT) {
            $params->date   = date('Y-m-d H:i:s', strtotime($data['timing']['startTime']));
        }

        return $params;
    }

}