<?php

class OpenWeatherMapStation extends DataObject {
	private static $db = array(
		'Name' => 'Varchar(255)',
		'OpenWeatherMapStationID' => 'Int',
		'Initialised' => 'Boolean',
		'Country' => 'Varchar(2)',
		'URLSegment' => 'Varchar(255)'
	);

	private static $summary_fields = array(
		'Name' => 'Station Name',
		'OpenWeatherMapStationID' => 'Station ID',
		'Country' => 'Country'
	);


	/**
	 * Add an index on the station id and url segment
	 */
	private static $indexes = array(
		'OpenWeatherMapStationID' => true,
		'URLSegment' => true
	);


	/* Sort weather stations by name in the admin interfaces */
	private static $default_sort = array('Name');

	public function DetailedForecast($days = 5, $render = true) {
		$forecast = OpenWeatherMapAPI::detailed_forecast($this->OpenWeatherMapStationID,$days);
		$forecasts = array();
		$list = $forecast->list;
		$result = new ArrayList();
		$ctr = 0;
		$ctrmax = 8*$days;

		// chart data
		$labels = array();
		$temperaturedata = array();
		$rainfalldata = array();
		$humiditydata = array();
		$cloudcoverdata = array();

		foreach($list as $forecastdata) {
			error_log('Iterating list for forecast data');
			$fc = $this->json_weather_to_data_object($forecastdata);
			if (isset($forecastdata->rain)) {
				$fc->Rain3Hours = $forecastdata->rain->{'3h'};
			} else {
				$fc->Rain3Hours = 0;
			}

			$dt = $forecastdata->dt;
			$ssdt = new SS_Datetime();
			$ssdt->setValue($dt);
			$fc->DateTime = $ssdt;
			$result->push($fc);
			$q = '"';
			array_push($labels, $q.$ssdt->Format('H:i').$q);
			array_push($temperaturedata, $q.$fc->TemperatureCurrent.$q);
			error_log('RAIN - pushing '.$fc->Rain3Hours);
			array_push($rainfalldata, $q.$fc->Rain3Hours.$q);
			array_push($humiditydata, $q.$fc->Humidity.$q);
			array_push($cloudcoverdata, $q.$fc->CloudCoverPercentage.$q);


			$ctr++;
			if ($ctr >= $ctrmax) {
				break;
			}
		}


		$labelcsv = implode(',', $labels);
		$temperaturecsv = implode(',', $temperaturedata);
		$rainfallcsv = implode(',', $rainfalldata);
		$cloudcovercsv = implode(',', $cloudcoverdata);
		$humiditycsv = implode(',', $humiditydata);

		error_log($rainfallcsv);


		// initialise variables for templates
		$varsarray = array(
			'Labels' => $labelcsv,
			'Temperatures' => $temperaturecsv,
			'Rainfall' => $rainfallcsv,
			'Humidity' => $humiditycsv,
			'CloudCover' => $cloudcovercsv,
			'Forecasts' => $result,
			'Station' => $this
		);

		$vars = new ArrayData($varsarray);


		// get the temperature JavaScript from a template.  Override in your own theme as desired
		$chartOptions = $vars->renderWith('ChartOptionsJS');
		$vars->setField('ChartOptions', $chartOptions);

		$temperatureJS = $vars->renderWith('TemperatureChartJS');
		$rainfallJS = $vars->renderWith('RainfallChartJS');
		$cloudhumidyJS = $vars->renderWith('CloudCoverHumidityChartJS');




		if ($render) {
			Requirements::css('openweathermap/css/openweathermap.css');
			Requirements::javascript('openweathermap/javascript/chart.min.js');
			Requirements::customScript(<<<JS
			$temperatureJS
			$rainfallJS
			$cloudhumidyJS
JS
);
			return $vars->renderWith('ForecastDetailed');
		} else {
			$vars->setField('ChartsJavascript', $temperatureJS."\n".$rainfallJS."\n".$cloudhumidyJS."\n");
		}

		$this->TemplateVars = $vars;
	}




	public function DailyForecast($days = 16, $render = true) {
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
			'Forecasts' => $result,
			'Station' => $this
		));

		$this->TemplateVars = $vars;


		if ($render) {
			Requirements::javascript('openweathermap/javascript/chart.min.js');
			return $vars->renderWith('ForecastDaily');
		}

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


	/**
	 * Access current weather as an object
	 * @return array or hash decoded JSON from API regarding current weather
	 */
	public function current_weather() {
		return OpenWeatherMapAPI::current_weather($this->OpenWeatherMapStationID);
	}


	/*
	Get the current weather for a station and render it using a template
	@param boolean $render  true to render straight away or false to populate template variables
	 */
	public function CurrentWeather($render = true) {
		$weather = OpenWeatherMapAPI::current_weather($this->OpenWeatherMapStationID);
		$sunrisedt = new SS_Datetime();
		$sunrisedt->setValue($weather->sys->sunrise);

		$sunsetdt = new SS_Datetime();
		$sunsetdt->setValue($weather->sys->sunset);

		$vars = new ArrayData(array(
			'Latitude' => $weather->coord->lat,
			'Longitude' => $weather->coord->lon,
			'Name' => $weather->name,
			'Country' => $weather->sys->country,
			'Sunrise' => $sunrisedt,
			'Sunset' => $sunsetdt,
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
			'CloudCoverPercentage' => $weather->clouds->all,
			'Station' => $this
		));

		$this->TemplateVars = $vars;

		if ($render) {
			return $vars->renderWith('CurrentWeather');
		}

	}


	/*
	Check if record needs initialised
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->prime();
		if (!$this->URLSegment) {

			$filter = URLSegmentFilter::create();
			$t = $filter->filter($this->Name);
			$this->URLSegment = $t;

			// Fallback to generic page name if path is empty (= no valid, convertable characters)
			if(!$t || $t == '-' || $t == '-1') {
				echo "Segment fallback\n";
				$this->URLSegment = "station-$this->OpenWeatherMapStationID";
			}

		}
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
