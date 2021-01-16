<?php declare(strict_types=1);

namespace HoneywellOld\Helper;


class DataFormats
{

	/**
	 * Method to transform Honeywell Remote data to object for local usage
	 *
	 * @param \stdClass $honeywellDevice
	 *
	 * @return \stdClass
	 */
	public function HoneywellDeviceToDevice(\stdClass $honeywellDevice) : \stdClass {
		$device = new \stdClass();

		$device->deviceID       = $honeywellDevice->deviceID;

		// state
		$state              = new \stdClass();
		$state->heating     = $honeywellDevice->operationStatus->mode === "Heat";
		$state->setTemp     = $honeywellDevice->changeableValues->heatSetpoint;
		$state->indoorTemp  = $honeywellDevice->indoorTemperature;
		$state->outdoorTemp = $honeywellDevice->outdoorTemperature;
		$state->serviceUp   = $honeywellDevice->service->mode === "Up";

		// settings
		$settings           = new \stdClass();
		$settings->units    = $honeywellDevice->units;
		$settings->name     = $honeywellDevice->name;
		$settings->type     = $honeywellDevice->deviceType;

		// add parameters
		$device->state          = $state;
		$device->settings       = $settings;

		return $device;
	}

	/**
	 * Method to transform Honeywell Remote data to object for local usage
	 *
	 * @param \stdClass $honeywellLocation
	 *
	 * @return \stdClass
	 */
	public function honeywellLocationToLocation(\stdClass $honeywellLocation) : \stdClass {
		$location = new \stdClass();

		$location->id         = $honeywellLocation->locationID;

		// geofence
		$geofence             = new \stdClass();
		$geofence->radius     = $honeywellLocation->geoFences[0]->radius;
		$geofence->occupated  = $honeywellLocation->geoFences[0]->geoOccupancy->withinFence > 0;

		// add parameters
		$location->geofence   = $geofence;

		return $location;
	}

	/**
	 * Method to transform fahrenheit to celsius
	 *
	 * @param float $fahrenheit
	 *
	 * @return float
	 */
	public function fahrenheitToCelsius(float $fahrenheit) : float {
		return 5 / 9 * ($fahrenheit - 32);
	}

	/**
	 * Method to transform celsius to fahrenheit
	 *
	 * @param float $celsius
	 *
	 * @return int
	 */
	public function celsiusToFahrenheit(float $celsius) : int {
		return intval($celsius * 9 / 5 + 32);
	}


}