<?php

class OpenWeatherMapStationsPage extends PageWithImage {
}

class OpenWeatherMapStationsPage_Controller extends PageWithImage_Controller {
	private static $allowed_actions = array(
		'StationsMap',
		'current',
		'shortterm',
		'longterm'
	);

	public function current() {
		$station = OpenWeatherMapStation::get()->
			filter('URLSegment', $this->request->param('ID'))->first();
		if (!$station) {
			$this->httpError(404);
		}
		$station->CurrentWeather(false);
		$vars = $station->TemplateVars;
		$this->Station = $station;
		$this->Title = 'Current Weather, '.$station->Name.', '.$station->Country;
		$this->dataRecord->Title = $this->dataRecord->Title.' - '.$station->Name.
			' - Current Weather';
		return array();
	}


	public function shortterm() {
		$station = OpenWeatherMapStation::get()->
			filter('URLSegment', $this->request->param('ID'))->first();
		if (!$station) {
			$this->httpError(404);
		}
		$station->DetailedForecast(5,false);
		$vars = $station->TemplateVars;
		$this->Station = $station;
		$this->Title = 'Short Term Forecast, '.$station->Name.', '.$station->Country;
		error_log('DATA RECORD:'.$this->dataRecord);
		$this->dataRecord->Title = $this->dataRecord->Title.' - '.$station->Name.
			' - Short Term Forecast';


		Requirements::css('openweathermap/css/openweathermap.css');
			Requirements::javascript('openweathermap/javascript/chart.min.js');
			Requirements::customScript(<<<JS
			{$vars->ChartsJavascript}
JS
);
		return array();
	}


	public function longterm() {
		$station = OpenWeatherMapStation::get()->
			filter('URLSegment', $this->request->param('ID'))->first();
		if (!$station) {
			$this->httpError(404);
		}
		$station->DailyForecast(16,false);

		$vars = $station->TemplateVars;

		$this->Station = $station;
		$this->Title = 'Long Term Forecast, '.$station->Name.', '.$station->Country;
		$this->dataRecord->Title = $this->dataRecord->Title.' - '.$station->Name.
			' - Long Term Forecast';



		/*

[0] => stdClass Object
                (
                    [dt] => 1429765200
                    [temp] => stdClass Object
                        (
                            [day] => 24.19
                            [min] => 21.94
                            [max] => 26.36
                            [night] => 22.07
                            [eve] => 24.23
                            [morn] => 24.19
                        )

                    [pressure] => 965.21
                    [humidity] => 100
                    [weather] => Array
                        (
                            [0] => stdClass Object
                                (
                                    [id] => 501
                                    [main] => Rain
                                    [description] => moderate rain
                                    [icon] => 10d
                                )

                        )

                    [speed] => 1.87
                    [deg] => 124
                    [clouds] => 92
                    [rain] => 11.12
                )

		 */

		//Requirements::css('openweathermap/css/openweathermap.css');
		//Requirements::javascript('openweathermap/javascript/chart.min.js');


		return array();
	}




	public function StationsMap() {
		$stations = OpenWeatherMapStation::get();
		$vars = array(
			'Link' => $this->Link()
		);
		$stations->setMarkerTemplateValues($vars);
		$map = $stations->getRenderableMap()->
			setZoom($this->owner->ZoomLevel)->
			setAdditionalCSSClasses('fullWidthMap')->
			setShowInlineMapDivStyle(true);
		$map->setEnableAutomaticCenterZoom(true);
		$map->setZoom(10);
		$map->setAdditionalCSSClasses('fullWidthMap');
		$map->setShowInlineMapDivStyle(true);
		$map->setClusterer(true);
		$map->CurrentURL = $this->Link();

		// calculate cache key
		$ck = 'mappablemarkers_'.$this->owner->ClassName;
		$ck .= '_'.$this->owner->ID;
		$ck .= '_'.OpenWeatherMapStation::get()->max('LastEdited');
		$ck = str_replace(':', '_', $ck);
		$ck = str_replace('-', '_', $ck);
		$ck = str_replace(' ', '_', $ck);

		//$map->setMarkersCacheKey($ck);

		return $map;
	}
}
