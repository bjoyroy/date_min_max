<?php

namespace UAB\DateMaxMinModule;

use ExternalModules\AbstractExternalModule;
use Form;
use Piping;

abstract class Page
{
    const DATA_ENTRY = 'DataEntry/index.php';
    const ONLINE_DESIGNER = 'Design/online_designer.php';
    const SURVEY = 'surveys/index.php';
    const SURVEY_THEME = 'Surveys/theme_view.php';
}

abstract class ResourceType
{
    const CSS = 'css';
    const HTML = 'html';
    const JS = 'js';
}

abstract class Validate
{
    static $date_validations = [
        'date_dmy',
        'date_mdy',
        'date_ymd',
        /*
        'datetime_dmy',
        'datetime_mdy',
        'datetime_ymd',
        'datetime_seconds_dmy',
        'datetime_seconds_mdy',
        'datetime_seconds_ymd',
        */
    ];

    // TODO: Pass in global variables and provide init function
    static function hasProjectID(): bool
    {
        return isset($project_id);
    }

    // TODO: Pass in global variables and provide init function
    static function hasRecordID(): bool
    {
        return isset($_GET['id']);
    }

    static function pageIs(string $page): bool
    {
        return PAGE == $page;
    }

    static function pageIsIn(array $pages): bool
    {
        return in_array(PAGE, $pages);
    }
}

class DateMaxMinModule extends AbstractExternalModule
{
    public $minDateTag = "@DATE-MIN";
    public $maxDateTag = "@DATE-MAX";

    // Given $Proj->metadata[$field_name] return whether the field 
    // is a text field and has date validation applied
    function isDateTypeField(array $field): bool
    {
        $isTextField = $field['element_type'] == 'text';
        $hasDateValidation = in_array($field['element_validation_type'], Validate::$date_validations);
        return $isTextField && $hasDateValidation;
    }

    function containsMinDateTag(?string $tags): bool
    {
        if (isset($tags) && strpos($tags, $this->minDateTag) !== false){
            return true;
        }
        return false;
    }

    function containsMaxDateTag(?string $tags): bool
    {
        if (isset($tags) && strpos($tags, $this->maxDateTag) !== false){
                return true; 
        }
        return false;
    }

    function includeSource(string $resourceType, string $path)
    {
        switch ($resourceType) {
            case ResourceType::CSS:
                echo '<link href="' . $this->getUrl($path) . '" rel="stylesheet">';
                break;
            case ResourceType::HTML:
                include($path);
                break;
            case ResourceType::JS:
                echo '<script src="' . $this->getUrl($path) . '"></script>';
                break;
            default:
                break;
        }
    }

    /*
     * Note: min and max validations set on the field do not prevent entering past or future dates.
     * $element_validation_min = $field['element_validation_min'];
     * $element_validation_max = $field['element_validation_max'];
    **/
    function redcap_every_page_top($project_id)
    {


        if (Validate::pageIs(Page::ONLINE_DESIGNER) && $project_id) {
            /*
            $this->initializeJavascriptModuleObject();
            $this->tt_addToJavascriptModuleObject('futureDateTag', $this->futureDateTag);
            $this->tt_addToJavascriptModuleObject('pastDateTag', $this->pastDateTag);

            $this->tt_addToJavascriptModuleObject('saturdayTag', $this->saturdayTag);
            $this->tt_addToJavascriptModuleObject('sundayTag', $this->sundayTag);
            $this->tt_addToJavascriptModuleObject('mondayTag', $this->mondayTag);
            $this->tt_addToJavascriptModuleObject('tuesdayTag', $this->tuesdayTag);
            $this->tt_addToJavascriptModuleObject('wednesdayTag', $this->wednesdayTag);
            $this->tt_addToJavascriptModuleObject('thursdayTag', $this->thursdayTag);
            $this->tt_addToJavascriptModuleObject('fridayTag', $this->fridayTag);



            $this->includeSource(ResourceType::JS, 'js/addActionTags.js');
            */
        } else if (Validate::pageIsIn(array(Page::DATA_ENTRY, Page::SURVEY, Page::SURVEY_THEME)) && isset($_GET['id'])) {
            //global $Proj;
            //$instrument = $_GET['page'];

            $this->initializeJavascriptModuleObject();
            $this->includeSource(ResourceType::JS, 'js/dateMinMax.js');
        }
    }

    function redcap_data_entry_form ( int $project_id, string $record, string $instrument, int $event_id, int $group_id, int $repeat_instance){
        //echo "Hello, world from data entry form!";
        //

        global $Proj;

        $instrument_fields = $Proj->forms[$instrument]['fields'];

/*
        echo "<pre>";
        print_r($instrument_fields);
        echo "</pre>";

        echo "<pre>";
        print_r($Proj->forms[$instrument]);
        echo "</pre>";
*/

        $fields = array();

        foreach (array_keys($Proj->forms[$instrument]['fields']) as $field_name) {
            $field = $Proj->metadata[$field_name];
            

            if($this->isDateTypeField($field)){

                $action_tags = $field['misc'];

                if($this->containsMinDateTag($action_tags)){
                    $fields[$field_name] = $field;
                } else if($this->containsMaxDateTag($action_tags)){
                    $fields[$field_name] = $field;
                }
                
            }
        }

        echo "<pre>";
        print_r($fields);
        echo "</pre>";

        foreach($fields as $key=>$arr){
            echo "<pre>";
            print_r($arr);
            echo "</pre>";
            $action_tags = $arr['misc'];
            //echo $action_tags;
            $minDatePre = Form::getValueInQuotesActionTag($action_tags, $this->minDateTag);
            $minDateValue = Piping::replaceVariablesInLabel($minDatePre, $record, $event_id,
                                    $repeat_instance, array(), false, null, false, "", 1, false, false, $instrument, null, true);
            echo "Min Date: " . $minDateValue;
        }
/*
        foreach($fields as $field_name => $arr){
            $action_tags = $arr['misc'];
            echo $action_tags";
            //$min_tag_val = Form::getValueInQuotesActionTag($action_tags, $this->minDateTag);
            //echo $min_tag_val;
            //echo "<br>";
        }
*/


    }

    function redcap_module_ajax($action, $payload, $project_id, $record, $instrument, $event_id, $repeat_instance, $survey_hash, $response_id, $survey_queue_hash, $page, $page_full, $user_id, $group_id){

        if ( $action == "instrument_structure_json" ) {
            //return array();
            
            global $Proj;

            $instrument_fields = $Proj->forms[$instrument]['fields'];

            $fields = array();



            foreach (array_keys($Proj->forms[$instrument]['fields']) as $field_name) {
                $field = $Proj->metadata[$field_name];

                $fields[$field_name] = $field;

            }

            return json_encode($fields, JSON_FORCE_OBJECT);
            


        }
    }
}
