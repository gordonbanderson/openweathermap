<?php
namespace WebOfTalent\OpenWeatherMap;

class OpenWeatherMapModelAdmin extends \SilverStripe\Admin\ModelAdmin {
	/**
	 * Managed models, here weather stations
	 */
	private static $managed_models = array('OpenWeatherMapStation');

	/* URL */
	private static $url_segment = 'openweathermap';

	/* Title of the model admin section */
	private static $menu_title = 'Open Weather Map';

	private static $menu_icon = 'weboftalent/openweathermap: /icons/cloud.png';
}
