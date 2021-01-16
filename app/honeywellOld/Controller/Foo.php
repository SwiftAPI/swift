<?php declare(strict_types=1);

namespace HoneywellOld\Controller;

use HoneywellOld\Model\Entity\Thermostat\LogThermostat;
use stdClass;
use Swift\GraphQl\Attributes\Argument;
use Swift\GraphQl\Attributes\Mutation;
use Swift\GraphQl\Attributes\Query;
use HoneywellOld\Model\Entity\Thermostat\Thermostat;
use Swift\GraphQl\Generators\EntityArgumentGenerator;
use Swift\GraphQl\Generators\EntityEnumGenerator;
use Swift\GraphQl\Generators\EntityInputGeneratorNew;
use Swift\Model\Entity\Arguments;
use Swift\Model\Types\ArgumentDirectionEnum;

/**
 * Class GraphQL
 * @package Honeywell\Controller
 */
final class Foo {

    /**
     * Foo constructor.
     *
     * @param Thermostat $thermostat
     * @param LogThermostat $logThermostat
     */
    public function __construct(
        private Thermostat $thermostat,
        private LogThermostat $logThermostat,
    ) {
    }

    /**
     * @param int $id
     *
     * @return stdClass|null
     */
    #[Query(type: Thermostat::class)]
    public function thermostat(int $id): ?stdClass {
        $thermostat = $this->thermostat->findOne(['id' => $id], true);

        //$thermostat->settings->name = 'Testing';
        $thermostat->state->heating = true;
        $thermostat->state->setTemp = 24.5;

        return $thermostat;
    }

    #[Mutation(type: LogThermostat::class)]
    public function addLog( #[Argument(type: LogThermostat::class, generator: EntityInputGeneratorNew::class)] $thermostat ): stdClass {
        return $this->logThermostat->save($thermostat);
    }

    /**
     * @param int $id
     *
     * @return stdClass
     */
    #[Query(type: LogThermostat::class)]
    public function log(int $id): stdClass {
        return $this->logThermostat->findOne(['id' => $id]);
    }

    /**
     * @param array|null $filter
     *
     * @return array
     */
    #[Query(type: LogThermostat::class, isList: true)]
    public function logs(#[Argument(type: Arguments::class, generator: EntityArgumentGenerator::class, generatorArguments: ['entity' => LogThermostat::class])] array|null $filter): array {
        $arguments = $filter ? new Arguments(...$filter) : new Arguments();

        return $this->logThermostat->find( state: array(), arguments: $arguments);
    }

}