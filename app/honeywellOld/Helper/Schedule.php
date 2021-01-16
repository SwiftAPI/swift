<?php declare(strict_types=1);

namespace HoneywellOld\Helper;


class Schedule
{
	/**
	 * @var DataFormats $helperDataFormats
	 */
	private $helperDataFormats;

	/**
	 * @var ScheduleList $helperScheduleList
	 */
	private $helperScheduleList;

	/**
	 * Schedule constructor.
	 *
	 * @param DataFormats  $helperDataFormats
	 * @param ScheduleList $helperScheduleList
	 */
	public function __construct(
		DataFormats $helperDataFormats,
		ScheduleList $helperScheduleList
	) {
		$this->helperDataFormats    = $helperDataFormats;
		$this->helperScheduleList   = $helperScheduleList;
	}

	/**
	 * Method to populate params
	 *
	 * @param array $data
	 *
	 * @return \stdClass
	 */
	public function populateParams(array $data) : \stdClass {
		$params = new \stdClass();

		if ($data['mode'] === 'recurring' || $data['mode'] === 'default') {
			$params->days       = $data['days'];
		}

		if ($data['mode'] === 'recurring' || $data['mode'] === 'default' || $data['mode'] === 'once' || $data['mode'] === 'till_next') {
			$params->startTime  = date('H:i', strtotime($data['timing']['startTime']));
			$params->endTime    = date('H:i', strtotime($data['timing']['endTime']));
		}

		if ($data['mode'] === 'once' || $data['mode'] === 'till_next') {
			$params->date   = date('Y-m-d H:i:s', strtotime($data['timing']['startTime']));
		}

		return $params;
	}

	/**
	 * Method to get list of schedules for a given period
	 *
	 * @param array  $defaultSchedules
	 * @param array  $recurringSchedules
	 * @param array  $onceSchedules
	 * @param array  $untilNextSchedules
	 * @param string $from
	 * @param string $to
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getList(array $defaultSchedules, array $recurringSchedules, array $onceSchedules, array $untilNextSchedules, string $from, string $to) : array {
		$this->helperScheduleList->populate($defaultSchedules, $recurringSchedules, $onceSchedules, $untilNextSchedules, $from, $to);

		return $this->helperScheduleList->getSchedule();
	}


}