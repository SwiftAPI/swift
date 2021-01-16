<?php declare(strict_types=1);

namespace HoneywellOld\Helper;

class ScheduleList
{
	/**
	 * @var array $schedule
	 */
	private $schedule = array();

	/**
	 * Schedules created during population
	 *
	 * @var array $addSchedules
	 */
	private $addSchedules = array();

	/**
	 * Method to get schedule
	 *
	 * @return array
	 */
	public function getSchedule() : array {
		$this->compile();
		return $this->schedule;
	}

	/**
	 * Method to populate schedules
	 *
	 * @param array  $defaultSchedules
	 * @param array  $recurringSchedules
	 * @param array  $onceSchedules
	 * @param array  $untilNextSchedules
	 * @param string $from
	 * @param string $to
	 *
	 * @throws \Exception
	 */
	public function populate(array $defaultSchedules, array $recurringSchedules, array $onceSchedules, array $untilNextSchedules, string $from, string $to) : void {
		$this->schedule = array();
		$this->addSchedules = array();
		$schedules  = array();

		ksort($defaultSchedules);
		ksort($recurringSchedules);
		ksort($onceSchedules);

		// Glue them all together
		$allSchedulesByID = array_replace(array(), $defaultSchedules, $recurringSchedules, $onceSchedules, $untilNextSchedules);

		$schedules  = $this->appendSchedules($schedules, $allSchedulesByID, $from, $to);
		if (!empty($this->addSchedules)) {
			$schedules  = $this->appendSchedules($schedules, $this->addSchedules, $from, $to);
		}

		$this->schedule = $schedules;
	}

	/**
	 * Method to compile schedule to sorted array
	 */
	public function compile() : void {
		$compiledSchedule = array();
		foreach ($this->schedule as $date => $schedule) {
			$dateFormat = date('Y-m-d', strtotime($date));

			foreach ($schedule as $scheduleItem) {
				if (!$scheduleItem->isVisible) {
					continue;
				}

				if (!property_exists($scheduleItem->params, 'isPartial')) {
					$scheduleItem->params->isPartial = false;
				}
				if ($scheduleItem->params->isPartial) {
					foreach ($scheduleItem->params->parts as $key => $part) {
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]                           = new \stdClass();
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->id                       = $scheduleItem->id;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->title                    = $scheduleItem->title;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->deviceID                 = $scheduleItem->deviceID;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->start                    = $scheduleItem->start;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->end                      = $scheduleItem->end;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->geofenced                = $scheduleItem->geofenced;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->temp                     = $scheduleItem->temp;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->geoAwayTemp              = $scheduleItem->geoAwayTemp;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->geoRadius                = $scheduleItem->geoRadius;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->type                     = $scheduleItem->type;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->params                   = new \stdClass();
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->isVisible                = $scheduleItem->isVisible;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->isOverridden             = $scheduleItem->isOverridden;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->params->days             = $scheduleItem->params->days;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->params->startTime        = $scheduleItem->params->parts[$key]->startTime;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->params->endTime          = $scheduleItem->params->parts[$key]->endTime;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->params->origStartTime    = $scheduleItem->params->origStartTime;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->params->origEndTime      = $scheduleItem->params->origEndTime;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->params->isPartial        = $scheduleItem->params->isPartial;
						$compiledSchedule[$dateFormat][$scheduleItem->params->parts[$key]->startTime]->created                  = date('Y-m-d H:i:s', strtotime($scheduleItem->created));
					}
				} else {
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]                           = new \stdClass();
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->id                       = $scheduleItem->id;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->title                    = $scheduleItem->title;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->deviceID                 = $scheduleItem->deviceID;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->start                    = $scheduleItem->start;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->end                      = $scheduleItem->end;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->geofenced                = $scheduleItem->geofenced;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->temp                     = $scheduleItem->temp;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->geoAwayTemp              = $scheduleItem->geoAwayTemp;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->geoRadius                = $scheduleItem->geoRadius;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->type                     = $scheduleItem->type;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params                   = new \stdClass();
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->isVisible                = $scheduleItem->isVisible;
					if (property_exists($compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params, 'isOverridden')) {
						$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params->isOverridden = $scheduleItem->params->isOverridden;
					} else {
						$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params->isOverridden = false;
					}
					if (property_exists($compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params, 'days')) {
						$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params->days         = $scheduleItem->params->days;
					}
					if (property_exists($compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params, 'date')) {
						$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params->date         = $scheduleItem->params->date;
					}
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params->startTime        = $scheduleItem->params->startTime;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params->endTime          = $scheduleItem->params->endTime;
					if (property_exists($compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params, 'origStartTime')) {
						$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params->origStartTime= $scheduleItem->params->origStartTime;
					}
					if (property_exists($compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params, 'origEndTime')) {
						$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params->origEndTime  = $scheduleItem->params->origEndTime;
					}
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->params->isPartial        = $scheduleItem->params->isPartial;
					$compiledSchedule[$dateFormat][$scheduleItem->params->startTime]->created                  = date('Y-m-d H:i:s', strtotime($scheduleItem->created));
				}
			}
			ksort($compiledSchedule[$dateFormat]);
		}
		ksort($compiledSchedule);

		// Validate compilation and make necessary fixes
		foreach ($compiledSchedule as $date => $scheduleDay) {
			// Create list of times in day and check if the end time matches the next items start time. If not adjust it.
			$timesMap   = array_keys($compiledSchedule[$date]);
			foreach ($scheduleDay as $timeKey => $scheduleItem) {
				$nextKey        = array_search($timeKey, $timesMap) + 1;
				if (array_key_exists($nextKey, $timesMap)) {
					$nextTimeKey    = $timesMap[$nextKey];
					$compiledSchedule[$date][$timeKey]->params->endTime = $compiledSchedule[$date][$nextTimeKey]->params->startTime;
				}
			}
		}

		$this->schedule = $compiledSchedule;
	}

	/**
	 * @param array  $currentSchedules
	 * @param array  $schedulesToAppend
	 * @param string $from
	 * @param string $to
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function appendSchedules(array $currentSchedules, array $schedulesToAppend, string $from, string $to) : array {
		$fromDate = new \DateTime($from, new \DateTimeZone('Europe/Amsterdam'));
		$toDate   = new \DateTime($to, new \DateTimeZone('Europe/Amsterdam'));
		$interval = \DateInterval::createFromDateString('1 day');
		$period   = new \DatePeriod($fromDate, $interval, $toDate);

		foreach ($schedulesToAppend as $scheduleToAppendID => $scheduleToAppend) {
			if (property_exists($scheduleToAppend, 'checked') && $scheduleToAppend->checked) {
				continue;
			}

			foreach ($period as $day) {
				$date   = $day->format('d-m-Y');

				if (!$this->shouldScheduleByAppended($scheduleToAppend, $date)) {
					// Check if schedule should be appended on this date
					continue;
				}

				if (!array_key_exists($date, $currentSchedules)) {
					$currentSchedules[$date] = array();
				}

				$scheduleToAppend->checked  = true;

				$currentSchedules[$date] = $this->appendSchedule($currentSchedules[$date], $scheduleToAppend);
			}
		}

		return $currentSchedules;
	}

	/**
	 * Method to append schedule to a given array of schedules
	 *
	 * @param array     $currentSchedule
	 * @param \stdClass $scheduleToAppend
	 *
	 * @return array    array of non conflicting schedules including the appended one
	 */
	private function appendSchedule(array $currentSchedule, \stdClass $scheduleToAppend) : array {
		$scheduleToAppend->isVisible    = true;

		if (empty($currentSchedule)) {
			$currentSchedule[]  = $scheduleToAppend;
			return $currentSchedule;
		}

		// Find and resolve conflict
		foreach ($currentSchedule as $key => $itemInSchedule) {
			if (!$itemInSchedule->isVisible) {
				continue;
			}
			// Check if they conflict
			$fixedConflicts = $this->checkAndResolveConflict($itemInSchedule, $scheduleToAppend);

			$currentSchedule[$key]  = $fixedConflicts['baseItem'];
			$scheduleToAppend       = $fixedConflicts['compareAgainst'];
		}

		// Append the schedule
		$currentSchedule[]  = $scheduleToAppend;

		return $currentSchedule;
	}

	/**
	 * Method to check and resolve conflicts between to scheduled objects
	 *
	 * @param \stdClass $baseItem
	 * @param \stdClass $compareAgainst
	 *
	 * @return array
	 */
	private function checkAndResolveConflict(\stdClass $baseItem, \stdClass $compareAgainst) : array {
		// First see if any conflict is going on
		// If not, return and don't do any following checks
		$compareAgainstStartTime    = strtotime($compareAgainst->params->startTime);
		$compareAgainstEndTime      = strtotime($compareAgainst->params->endTime);
		$baseItemStartTime          = strtotime($baseItem->params->startTime);
		$baseItemEndTime            = strtotime($baseItem->params->endTime);
		if ($baseItemEndTime <= $compareAgainstStartTime || $baseItemStartTime >= $compareAgainstEndTime) {
			// No conflict at all
			return array(
				'baseItem'          => $baseItem,
				'compareAgainst'    => $compareAgainst,
			);
		}

		// If any of these are true, the baseItem has precedence over the compareAgainst item
		// By default the latter has precedence
		$compareAgainstIsBoss  = true;
		if ($baseItem->type === 'recurring' && $compareAgainst->type === 'default') {
			$compareAgainstIsBoss = false;
		}
		if ($baseItem->type === 'once' && ($compareAgainst->type === 'default' || $compareAgainst->type === 'recurring')) {
			$compareAgainstIsBoss = false;
		}
		if ($baseItem->type === 'till_next' && ($compareAgainst->type === 'default' || $compareAgainst->type === 'recurring' || $compareAgainst->type === 'once')) {
			$compareAgainstIsBoss = false;
		}

		$bossItem       = $compareAgainstIsBoss ? $compareAgainst : $baseItem;
		$nonBossItem    = $compareAgainstIsBoss ? $baseItem : $compareAgainst;

		// Find and resolve conflicts
		$bossItemStartTime      = strtotime($bossItem->params->startTime);
		$bossItemEndTime        = strtotime($bossItem->params->endTime);
		$nonBossItemStartTime   = strtotime($nonBossItem->params->startTime);
		$nonBossItemEndTime     = strtotime($nonBossItem->params->endTime);

		$continueCheck = true;

		// Check full override
		if ($nonBossItemStartTime >= $bossItemStartTime && $nonBossItemEndTime <= $bossItemEndTime) {
			// NonBossItem is fully overridden by boss item
			// No need to adjust anything, just say it's not visible anymore
			$nonBossItem->isVisible = false;
			$continueCheck = false;
		}

		// Check override in the middle
		if ($continueCheck && $bossItemStartTime > $nonBossItemStartTime && $bossItemEndTime < $nonBossItemEndTime) {

			// Reset endTime of nonBossItem to bossItemStartTime
			$nonBossItem->params->origStartTime = !property_exists($nonBossItem->params, 'origStartTime') ? $nonBossItem->params->startTime : $nonBossItem->params->origStartTime;
			$nonBossItem->params->origEndTime   = !property_exists($nonBossItem->params, 'origEndTime') ? $nonBossItem->params->endTime : $nonBossItem->params->origEndTime;

			$nonBossItem->params->isPartial     = true;
			if (!property_exists($nonBossItem->params, 'parts')) {
				$nonBossItem->params->parts     = array();
			}

			$newPartBeforeSplit             = new \stdClass();
			$newPartBeforeSplit->type       = 'time';
			$newPartBeforeSplit->startTime  = $nonBossItem->params->startTime;
			$newPartBeforeSplit->endTime    = $bossItem->params->startTime;

			$newPartAfterSplit              = new \stdClass();
			$newPartAfterSplit->type        = 'time';
			$newPartAfterSplit->startTime   = $bossItem->params->endTime;
			$newPartAfterSplit->endTime     = $nonBossItem->params->endTime;

			$newParts   = array($newPartBeforeSplit, $newPartAfterSplit);

			$nonBossItem->params->parts = $newParts;
			$nonBossItem->isOverridden  = true;

			$continueCheck = false;
		}

		// Check partial override on start
		if ($continueCheck && $bossItemStartTime <= $nonBossItemStartTime && $bossItemEndTime < $nonBossItemEndTime) {
			// Reset startTime of nonBossItem to bossItemEndTime
			$nonBossItem->params->origStartTime = !property_exists($nonBossItem->params, 'origStartTime') ? $nonBossItem->params->startTime : $nonBossItem->params->origStartTime;
			$nonBossItem->params->origEndTime   = !property_exists($nonBossItem->params, 'origEndTime') ? $nonBossItem->params->endTime : $nonBossItem->params->origEndTime;
			$nonBossItem->params->startTime     = $bossItem->params->endTime;
			$nonBossItem->isOverridden          = true;

			$continueCheck = false;
		}

		// Check partial override on end
		if ($continueCheck && $bossItemEndTime >= $nonBossItemEndTime && $bossItemStartTime > $nonBossItemStartTime) {
			// Reset endTime of nonBossItem to bossItemStartTime
			$nonBossItem->params->origStartTime = !property_exists($nonBossItem->params, 'origStartTime') ? $nonBossItem->params->startTime : $nonBossItem->params->origStartTime;
			$nonBossItem->params->origEndTime   = !property_exists($nonBossItem->params, 'origEndTime') ? $nonBossItem->params->endTime : $nonBossItem->params->origEndTime;
			$nonBossItem->params->endTime       = $bossItem->params->startTime;
			$nonBossItem->isOverridden          = true;

			$continueCheck = false;
		}

		return array(
			'baseItem'          => $compareAgainstIsBoss ? $nonBossItem : $bossItem,
			'compareAgainst'    => $compareAgainstIsBoss ? $bossItem : $nonBossItem,
		);
	}

	/**
	 * Method to verify if a schedule should be appended on a given date
	 *
	 * @param \stdClass $scheduleToAppend
	 * @param string    $comparisonDate
	 *
	 * @return bool
	 */
	private function shouldScheduleByAppended(\stdClass $scheduleToAppend, string $comparisonDate) : bool {
		$scheduleToAppendValidFrom  = strtotime($scheduleToAppend->start);
		$scheduleToAppendValidUntil = strtotime($scheduleToAppend->end);
		$comparisonDateInTime       = strtotime($comparisonDate);

		if ($scheduleToAppend->type === 'default' || $scheduleToAppend->type === 'recurring') {
			if ($scheduleToAppendValidFrom > $comparisonDateInTime || $scheduleToAppendValidUntil < $comparisonDateInTime) {
				// Schedule to append has either not started or has expired
				return false;
			}

			if (property_exists($scheduleToAppend->params, 'days')) {
				// Check schedule should be display on this day, if not; return false
				$dayName = strtolower(date('l', strtotime($comparisonDate)));

				if ($scheduleToAppend->params->days === 'workweek' && ($dayName === 'saturday' || $dayName === 'sunday')) {
					return false;
				}

				if ($scheduleToAppend->params->days === 'weekend' && ($dayName !== 'saturday' && $dayName !== 'sunday')) {
					return false;
				}
			}
		} elseif ($scheduleToAppend->type === 'once' || $scheduleToAppend->type === 'till_next') {
			if (property_exists($scheduleToAppend->params, 'date') &&
				(date('d-m-Y',strtotime($comparisonDate)) !== date('d-m-Y', strtotime($scheduleToAppend->params->date)))) {
				return false;
			}
		}

		return true;
	}

	private function isScheduleActiveNow(string $dateToCheck, string $startTime, string $endTime) : bool {
		if (date('d-m-Y', strtotime('now')) !== date('d-m-Y', strtotime($dateToCheck))) {
			// It's not today
			return false;
		}

		$dateTimeStamp = date('H:i', strtotime('now'));
		$nowTime    = strtotime($dateTimeStamp);
		$startTime  = strtotime($startTime);
		$endTime    = strtotime($endTime);



		if ($nowTime > $startTime && $nowTime <= $endTime) {
			return true;
		}

		return false;
	}

}