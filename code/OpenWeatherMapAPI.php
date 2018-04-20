<?php

class OpenWeatherMapAPI {
    /** @var string Denote metric units */
    const METRIC='metric';

    /** @var string Denotes imperial units */
    const IMPERIAL = 'imperial';

	/**
	 * Obtain the API key from configuration file
	 * @return {string} Configured API key for Open Weather Map
	 */
	private static function get_api_key() {
		return \SilverStripe\Core\Config\Config::inst()->get('WebOfTalent\OpenWeatherMap\OpenWeatherMapAPI', 'api_key');
	}

	/**
	 * Centralised method to get the cache for open weather map data
	 * @return SS_Cache SilverStripe cache object
	 */
	private static function get_cache() {
        return \SilverStripe\Core\Injector\Injector::inst()->get(\Psr\SimpleCache\CacheInterface::class . '.openweathermap');
	}


	/**
	 * Obtain a JSON object utilising the API if needbe, but taking into account hit rates against
	 * the API - documentation says not to repeat URLS more than every 10 mins
	 * @param  [string] $url JSON service URL for the required data
	 * @return {object}      Array or struct object decoded from returned or cached JSON data
	 */
	private static function cache_friendly_json_from_url($url) {
		$cache = self::get_cache();
		$apikey = self::get_api_key();
		$apiurl = $url.'&APPID='.$apikey;
		$cachekey = hash('ripemd160',$apiurl);

		if (!($json = $cache->get($cachekey))) {
			$json = file_get_contents($apiurl);
			$cache->set($cachekey, $json);
			error_log("CK MISS");
		} else {
			error_log("CK HIT");
		};

		return json_decode($json);
	}

	/**
	 * Forecasted weather data from a station of given OpenWeatherMap id
	 * @param  integer $stationid OpenWeatherMap id of station
	 * @return struct Object decoded from JSON API representing forecasted weather
	 */
	public static function detailed_forecast($stationid, $units = 'metric') {
		$url = "http://api.openweathermap.org/data/2.5/forecast?id={$stationid}&units=" . $units;
		// FIXME take account of the 3 hr refresh with forecasts here?
		return self::cache_friendly_json_from_url($url);
	}


	/**
	 * Daily forecast for a station of given OpenWeatherMap id
	 * @param  integer $stationid OpenWeatherMap id of station
	 * @param  integer numberOfDays Number of days to forecast, maximum of 16
	 * @return struct Object decoded from JSON API representing forecasted weather
	 */
	public static function daily_forecast($stationid, $numberOfDays, $units = 'metric') {
		$url = "http://api.openweathermap.org/data/2.5/forecast/daily?id={$stationid}&cnt=";
		$url .= "{$numberOfDays}&units=" . $units;
		// FIXME take account of the 3 hr refresh with forecasts here?
		return self::cache_friendly_json_from_url($url);
	}


	/**
	 * Current weather data from a station of given OpenWeatherMap id
	 * @param  integer $stationid OpenWeatherMap id of station
	 * @return struct Object decoded from JSON API representing current weather
	 */
	public static function current_weather($stationid, $units = 'metric') {
		$url = "http://api.openweathermap.org/data/2.5/weather?id={$stationid}&units=" . $units;
		return self::cache_friendly_json_from_url($url);
	}


	/**
	 * Get a list of stations from the API near a given latitude/longitude
	 * @param  float  $lat latitude of point being checked
	 * @param  float  $lon longitude of point being checked
	 * @param  integer $cnt number of stations to return, default 30
	 * @return array array of station data
	 */
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
