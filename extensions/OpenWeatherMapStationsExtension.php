<?php
/**
 * Associate a list of weather map stations to a Page or DataObject
 */
class OpenWeatherMapStationsExtension extends DataExtension {
	private static $many_many = array(
		'OpenWeatherMapStations' => 'OpenWeatherMapStation'
	);


	public function updateCMSFields(FieldList $fields) {
		$gridConfig2 = GridFieldConfig_RelationEditor::create();
		$gridConfig2->getComponentByType(
			'GridFieldAddExistingAutocompleter')->
			setSearchFields(array('Name', 'Country', 'OpenWeatherMapStationID')
		);
		$gridConfig2->getComponentByType('GridFieldPaginator')->setItemsPerPage(100);
		$gridField2 = new GridField("Weather Stations",
			"Weather Stations:",
			$this->owner->OpenWeatherMapStations(),
			$gridConfig2
		);
		$fields->addFieldToTab("Root.WeatherStations", $gridField2);
	}
}
