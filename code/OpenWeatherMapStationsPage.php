<?php

class OpenWeatherMapStationsPage extends Page {

}

class OpenWeatherMapStationsPage_Controller extends Page_Controller {
	private static $allowed_actions = array(
		'StationsMap',
		'current',
		'shortterm',
		'longterm'
	);

	public function current() {
		$station = DataObject::get_by_id('OpenWeatherMapStation', $this->request->param('ID'));
		$station->CurrentWeather(false);
		$vars = $station->TemplateVars;
		$this->Station = $station;
		$this->Title = 'Current Weather, '.$station->Name.', '.$station->Country;
		return array();
	}


	public function shortterm() {
		$station = DataObject::get_by_id('OpenWeatherMapStation', $this->request->param('ID'));
		$station->DetailedForecast(5,false);
		$vars = $station->TemplateVars;
		$this->Station = $station;
		$this->Title = 'Short Term Forecast, '.$station->Name.', '.$station->Country;


		Requirements::css('openweathermap/css/openweathermap.css');
			Requirements::javascript('openweathermap/javascript/chart.min.js');
			Requirements::customScript(<<<JS
			{$vars->ChartsJavascript}
JS
);
		return array();
	}


	public function longterm() {
		$station = DataObject::get_by_id('OpenWeatherMapStation', $this->request->param('ID'));
		$station->DailyForecast(16,false);
		$vars = $station->TemplateVars;
		$this->Station = $station;
		$this->Title = 'Long Term Forecast, '.$station->Name.', '.$station->Country;

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

		$map->setMarkersCacheKey($ck);

		return $map;
	}
}
