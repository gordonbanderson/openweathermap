<?php

class OpenWeatherMapStation extends DataObject {
	private static $db = array(
		'Name' => 'Varchar(255)',
		'OpenWeatherMapStationID' => 'Int',
		'Initialised' => 'Boolean',
		'Country' => 'Varchar(2)'
	);

	private static $summary_fields = array(
		'Name' => 'Station Name',
		'OpenWeatherMapStationID' => 'Station ID',
		'Country' => 'Country'
	);


	/**
	 * Add an index on the station id
	 */
	private static $indexes = array(
		'OpenWeatherMapStationID' => true
	);


	/* Sort weather stations by name in the admin interfaces */
	private static $default_sort = array('Name');

	public function Forecast($days = 5) {
		// 3hr refresh

	}


	/**
	 * Get a list of nearby weather stations and return them as SilverStripe objects.  Note this
	 * will create records for any missing stations in the database
	 */
	public function NearByWeatherStations() {
		$nearby = OpenWeatherMapAPI::nearby_weather_stations($this->Lat,$this->Lon);
		$stations = new ArrayList();

		for ($i=0; $i < sizeof($nearby); $i++) {
			$owms = new DataObject();

			$station = $nearby[$i];
			$owms->Distance = $station->distance;
			$stationdata = $station->station;
			$owms->OpenWeatherMapStationID = $stationdata->id;
			$owms->Name = $stationdata->name;
			$coords = $stationdata->coord;
			$owms->Lat = $coords->lat;
			$owms->Lon = $coords->lon;
			$stations->push($owms);
		}

		$vars = new ArrayData(array(
			'WeatherStations' => $stations
		));

		return $vars->renderWith('NearbyWeatherStations');
	}


	public function CurrentWeather() {
		$weather = OpenWeatherMapAPI::current_weather($this->OpenWeatherMapStationID);
		$vars = new ArrayData(array(
			'Latitude' => $weather->coord->lat,
			'Longitude' => $weather->coord->lat,
			'Name' => $weather->name,
			'Country' => $weather->sys->country,
			'Sunrise' => $weather->sys->sunrise,
			'Sunset' => $weather->sys->sunset,
			'WeatherDescription' => $weather->weather[0]->description,

			'WeatherMain' => $weather->weather[0]->main,
			'WeatherIconURL' => 'http://openweathermap.org/img/w/'.$weather->weather[0]->icon.'.png',
			'WindSpeed' => $weather->wind->speed,
			'WindDirection' => $weather->wind->deg,
			'TemperatureCurrent' => $weather->main->temp,
			'TemperatureMin' => $weather->main->temp_min,
			'TemperatureMax' => $weather->main->temp_max,
			'Pressure' => $weather->main->pressure,
			'PressureSeaLevel' => $weather->main->sea_level,
			'PressureGroundLevel' => $weather->main->grnd_level,
			'Humidity' => $weather->main->humidity,
			'CloudCoverPercentage' => $weather->clouds->all
		));

		//return $weather;
		return $vars->renderWith('CurrentWeather');
	}


	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->prime();
	}


	public function prime() {
		if (!$this->Initialised) {
			$weather = OpenWeatherMapAPI::current_weather($this->OpenWeatherMapStationID);
			$this->Lat = $weather->coord->lat;
			$this->Lon = $weather->coord->lon;
			$this->Name = $weather->name;
			$this->Country = $weather->country;
			$this->Zoom = 17;
			$this->Initialised = true;
		}
	}
}
