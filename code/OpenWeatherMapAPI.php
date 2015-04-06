<?php

class OpenWeatherMapAPI {

	/**
	 * Obtain the API key from configuration file
	 * @return {string} Configured API key for Open Weather Map
	 */
	private static function get_api_key() {
		$key = Config::inst()->get('OpenWeatherMap', 'api_key');
		return $key;
	}

	/**
	 * [get_cache description]
	 * @return [type] [description]
	 */
	private static function get_cache() {
		$cache = SS_Cache::factory('openweathermap');
		return $cache;
	}


	private static function cache_friendly_json_from_url($url) {
		$cache = self::get_cache();
		$cachekey = hash('ripemd160',$url);
		if (!($json = $cache->load($cachekey))) {
			$json = file_get_contents($url);
			$cache->save($json, $cachekey);
			error_log("CK MISS");
		} else {
			error_log("CK HIT");
		};

		return json_decode($json);
	}


	public static function forecast($stationid, $numberOfDays) {
		$url = "http://api.openweathermap.org/data/2.5/forecast?id={$stationid}&cnt={$numberOfDays}&units=metric";
		// FIXME take account of the 3 hr refresh with forecasts here?
		return self::cache_friendly_json_from_url($url);
	}


	public static function current_weather($stationid) {
		$url = "http://api.openweathermap.org/data/2.5/weather?id={$stationid}&units=metric";
		return self::cache_friendly_json_from_url($url);
	}


	public static function nearby_weather_stations($lat,$lon,$cnt = 30) {
		$url = "http://api.openweathermap.org/data/2.5/station/find?lat={$lat}&lon={$lon}&cnt={$cnt}";
		return self::cache_friendly_json_from_url($url);
	}


	//http://api.openweathermap.org/data/2.5/forecast?callback=?&id=1608133&units=metric
	//http://api.openweathermap.org/data/2.5/forecast/daily?callback=?&id=1608133&units=metric&cnt=1
	//

	/*
	URLS for services
	Nearby weather stations to a lat/long coordinate
	http://api.openweathermap.org/data/2.5/station/find?lat=55&lon=37&cnt=30

	historical data for a station
	http://api.openweathermap.org/data/2.5/history/station?id=5091&type=tick


	---- bounding boxes ----
	bounding box
	http://api.openweathermap.org/data/2.5/box/city?bbox=12,32,15,37,10&cluster=yes

	list of places
	http://api.openweathermap.org/data/2.5/group?id=524901,703448,2643743&units=metric


	forecast
	http://api.openweathermap.org/data/2.5/forecast?id=524901

	lat lon bounded forecast for x days
	http://api.openweathermap.org/data/2.5/forecast/daily?lat=35&lon=139&cnt=10&mode=json



	 */
}
