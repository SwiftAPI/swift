<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Honeywell\Controller;

use Honeywell\Model\Thermostat;
use Honeywell\Service\ThermostatService;
use Honeywell\Types\ScheduleType;
use Honeywell\Types\ThermostatState;
use JetBrains\PhpStorm\Pure;
use stdClass;
use Swift\Controller\Controller;
use Swift\GraphQl\Attributes\Argument;
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;
use Swift\Kernel\TypeSystem\Defaults\Datetime\WeekdaysEnum;
use Swift\Router\HTTPRequest;

/**
 * Class ThermostatController
 * @package Honeywell\Controller
 */
class ThermostatController extends Controller {

    /**
     * Thermostat constructor.
     *
     * @param HTTPRequest $request
     * @param ThermostatService $thermostatService
     */
    #[Pure] public function __construct(
        private HTTPRequest $request,
        private ThermostatService $thermostatService,
    ) {

        parent::__construct($this->request);
    }

    #[Query(name: 'thermostat', type: Thermostat::class)]
    public function graphqlGetThermostatById( int $id ): ?stdClass {
        return $this->thermostatService->getThermostatById($id);
    }

    #[Query(name: 'thermostatState')]
    public function graphqlGetThermostatStateById( int $id ): ?ThermostatState {
        return $this->thermostatService->getThermostatStateById($id);
    }

    #[Mutation(name: 'setOverrideScheduleTillNext', type: ScheduleType::class)]
    public function graphqlSetOverrideScheduleTillNext( float $temp ): ScheduleType {
        return $this->thermostatService->setOverrideScheduleTillNext($temp);
    }
}