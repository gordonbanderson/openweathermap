<?php

class OpenWeatherMapStation extends DataObject {
	private static $db = array(
		'Name' => 'Varchar(255)',
		'OpenWeatherMapStationID' => 'Int',
		'Initialised' => 'Boolean',
		'Country' => 'Varchar(2)'
	);


	public function Forecast($days = 5) {
		// 3hr refresh

	}


	public function NearByWeatherStations($prime = false) {
		$nearby = OpenWeatherMapAPI::nearby_weather_stations($this->Lat,$this->Lon);
		print_r($nearby);

		// FIXME avoid dupes
		if ($prime) {
			for ($i=0; $i < sizeof($nearby); $i++) {
				error_log($i);
				$owms = new OpenWeatherMapStation();
				$station = $nearby[$i];
				$stationdata = $station->station;
				$owms->OpenWeatherMapStationID = $stationdata->id;
				$owms->Name = $stationdata->name;
				$coords = $stationdata->coord;
				error_log('COORS');
				error_log(print_r($coords,1));
				$owms->Lat = $coords->lat;
				$owms->Lon = $coords->lon;
				$owms->Initialised = true;
				$owms->write();
			}
		}


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



		/*
		{
    "coord": {
        "lon": 100.51,
        "lat": 13.86
    },
    "sys": {
        "message": 0.007,
        "country": "TH",
        "sunrise": 1428102703,
        "sunset": 1428147011
    },
    "weather": [{
        "id": 800,
        "main": "Clear",
        "description": "Sky is Clear",
        "icon": "01d"
    }],
    "base": "cmc stations",
    "main": {
        "temp": 35.658,
        "temp_min": 35.658,
        "temp_max": 35.658,
        "pressure": 1017.13,
        "sea_level": 1017.58,
        "grnd_level": 1017.13,
        "humidity": 37
    },
    "wind": {
        "speed": 5.61,
        "deg": 194.505
    },
    "clouds": {
        "all": 0
    },
    "dt": 1428134922,
    "id": 1608133,
    "name": "Mueang Nonthaburi",
    "cod": 200
}
		 */

		//return $weather;
		return $vars->renderWith('CurrentWeather');
	}


	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->prime();
	}


	public function prime() {
		if (!$this->Initialised) {
			error_log("PRIMING ".$this->OpenWeatherMapStationID);
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
