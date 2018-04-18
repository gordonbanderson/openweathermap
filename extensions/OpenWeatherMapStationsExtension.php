<?php

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataExtension;

/**
 * Associate a list of weather map stations to a Page or DataObject
 */
class OpenWeatherMapStationsExtension extends DataExtension
{
    private static $many_many = array(
        'OpenWeatherMapStations' => 'OpenWeatherMapStation'
    );


    public function updateCMSFields(FieldList $fields)
    {
        $gridConfig2 = GridFieldConfig_RelationEditor::create();
        $gridConfig2->getComponentByType(
            GridFieldAddExistingAutocompleter::class
        )->
            setSearchFields(array('Name', 'Country', 'OpenWeatherMapStationID'));
        $gridConfig2->getComponentByType(GridFieldPaginator::class)->setItemsPerPage(100);
        $gridField2 = new GridField(
            "Weather Stations",
            "Weather Stations:",
            $this->owner->OpenWeatherMapStations(),
            $gridConfig2
        );
        $fields->addFieldToTab("Root.WeatherStations", $gridField2);
    }
}
