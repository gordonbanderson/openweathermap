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

	public function DetailedForecast($days = 5) {
		$forecast = OpenWeatherMapAPI::detailed_forecast($this->OpenWeatherMapStationID,$days);
		$forecasts = array();
		$list = $forecast->list;
		$result = new ArrayList();
		foreach($list as $forecastdata) {
			$fc = $this->json_weather_to_data_object($forecastdata);
			if (isset($forecastdata->rain)) {
				$fc->Rain3Hours = $forecastdata->rain->{'3h'};
			}

			$fc->DateTime = $forecastdata->dt_txt;
			$result->push($fc);
		}


		$vars = new ArrayData(array(
			'Forecasts' => $result
		));

		return $vars->renderWith('ForecastPerThreeHours');
	}


	public function DailyForecast($days = 16) {
		$forecast = OpenWeatherMapAPI::daily_forecast($this->OpenWeatherMapStationID,$days);
		$forecasts = array();
		$list = $forecast->list;
		$result = new ArrayList();
		foreach($list as $forecastdata) {
			$fc = $this->json_weather_to_data_object($forecastdata);
			if (isset($forecastdata->rain)) {
				$fc->Rainfall = $forecastdata->rain;
			}

			$result->push($fc);
		}


		$vars = new ArrayData(array(
			'Forecasts' => $result
		));

		return $vars->renderWith('ForecastPerThreeHours');
	}


	/**
	 * Get a list of nearby weather stations and render them using a template
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


	/*
	Get the current weather for a station and render it using a template
	 */
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


	/*
	Check if record needs initialised
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->prime();
	}


	/*
	If a station has not been initialised get the current weather and use that to get the
	name,coordinates and country of that station, populating the database record. Only do this
	once of course.
	 */
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


	/**
	 * Helper method to convert weather data in JSON format to a SilverStripe DataObject
	 * It appears several times in the API
	 * @param  [type] $weather [description]
	 * @return [type]          [description]
	 */
	private function json_weather_to_data_object($weather) {
		$do = new DataObject();
		//$do->Latitude = $weather->coord->lat;
		//$do->Longitude = $weather->coord->lat;
		//$do->Name = $weather->name;
		//$do->Country = $weather->sys->country;
		//$do->Sunrise = $weather->sys->sunrise;
		//$do->Sunset = $weather->sys->sunset;
		$do->WeatherDescription = $weather->weather[0]->description;
		$do->WeatherMain = $weather->weather[0]->main;
		$do->WeatherIconURL = 'http://openweathermap.org/img/w/'.$weather->weather[0]->icon.'.png';

		if (isset($weather->wind)) {
			$do->WindSpeed = $weather->wind->speed;
			$do->WindDirection = $weather->wind->deg;
		}

		if (isset($weather->main)) {
			$do->TemperatureCurrent = $weather->main->temp;
			$do->TemperatureMin = $weather->main->temp_min;
			$do->TemperatureMax = $weather->main->temp_max;
			$do->Pressure = $weather->main->pressure;
			$do->PressureSeaLevel = $weather->main->sea_level;
			$do->PressureGroundLevel = $weather->main->grnd_level;
			$do->Humidity = $weather->main->humidity;
		}

		if (isset($weather->clouds)) {
			// variation in data output here :(
			if (isset($weather->clouds->all)) {
				$do->CloudCoverPercentage = $weather->clouds->all;
			} else {
				$do->CloudCoverPercentage = $weather->clouds;
			}

		}

		return $do;
	}
}
