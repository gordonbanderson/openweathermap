<?php

class OpenWeatherMapController extends Controller {
	private static $allowed_actions = array(
			'import_country',
			'populate_urlsegments',
			'add_nearby_stations'
	);

	/**
	 Add stations near a given station.  There are more than just in the city list
	 */
	public function add_nearby_stations() {
		$canAccess = ( Director::isDev() || Director::is_cli() || Permission::check( "ADMIN" ) );
        if ( !$canAccess ) {
        	return Security::permissionFailure( $this );
        }

        $station = OpenWeatherMapStation::get()->
			filter('URLSegment', $this->request->param('ID'))->first();
		if (!$station) {
			$this->httpError(404);
		}

		$station->NearByWeatherStations(false);
		$nearby = $station->TemplateVars->WeatherStations;
		foreach ($nearby as $nearbystation) {
			echo "{$nearbystation->Distance}\t{$nearbystation->Name}\n";
			$owms = OpenWeatherMapStation::get()->filter('OpenWeatherMapStationID',
									$nearbystation->OpenWeatherMapStationID)->first();
			if (!$owms) {
				echo "\tNew station\n";
				$owms = new OpenWeatherMapStation();
				$owms->Lat = $nearbystation->Lat;
				$owms->Lon = $nearbystation->Lon;
				$owms->Name = $nearbystation->Name;
				$owms->OpenWeatherMapStationID = $nearbystation->OpenWeatherMapStationID;
				$owms->Initialised = true;
				$owms->write();

			}
		}


	}

	/**
	 * Popoulate URLSegments after that field was introduced
	 */
	public function populate_urlsegments() {
		// check access permissions, we don't want this to be public
		$canAccess = ( Director::isDev() || Director::is_cli() || Permission::check( "ADMIN" ) );
        if ( !$canAccess ) {
        	return Security::permissionFailure( $this );
        }

		$stations = OpenWeatherMapStation::get();
		foreach ($stations->getIterator() as $station) {
			$station->write();
			echo "SEGMENT:".$station->URLSegment."\n";
		}
	}


	/**
	 * Import countries from bulk JSON file
	 */
	public function import_country() {
		// check access permissions, we don't want this to be public
		$canAccess = ( Director::isDev() || Director::is_cli() || Permission::check( "ADMIN" ) );
        if ( !$canAccess ) {
        	return Security::permissionFailure( $this );
        }

		$cityfile = BASE_PATH.'/openweathermap/bulk/city.list.json';
		if (file_exists($cityfile)) {
			$country = $this->request->param('ID');
			echo "Importing stations for country ".$country."\n";
			$checkstring = '"country":"'.$country.'"';
			$file = fopen($cityfile, "r");
			$ctr = 0;
			while(!feof($file)){
			    $line = fgets($file);
			    if (strrpos($line, $checkstring) > 0) {
			    	$ctr++;

			    	// each line is information about the weather station in JSON format
			    	$stationinfo = json_decode($line);

			    	$id = $stationinfo->_id;
			    	$name = $stationinfo->name;
			    	$lat = $stationinfo->coord->lat;
			    	$lon = $stationinfo->coord->lon;

			    	// check for the station already existing or not
			    	$stationct = OpenWeatherMapStation::get()->filter('OpenWeatherMapStationID', $id)->count();
			    	if ($stationct == 0) {
			    		$station = new OpenWeatherMapStation();
			    		$station->OpenWeatherMapStationID = $id;
			    		$station->Name = $name;
			    		$station->Lat = $lat;
			    		$station->Lon = $lon;
			    		$station->Zoom = 17;
			    		$station->Initialised = true;
			    		$station->Country = $country;
			    		$station->write();
			    		echo "IMPORTED: {$id},{$name},{$lat},{$lon}\n";
			    	} else {
			    		echo "EXISTS: {$id},{$name},{$lat},{$lon}\n";
			    	}
			    }

			}
			fclose($file);

		} else {
			echo "The file <YOUR SITE ROOT>/openweathermap/bulk/city.list.json must exist\n";
			echo "To create it execute the following in a console\n\n";
			echo "cd /root/of/silverstripe-project/openweathermap/bulk\n";
			echo "wget http://78.46.48.103/sample/city.list.json.gz\n";
			echo "gunzip city.list.json.gz\n";
		}
	}
}
