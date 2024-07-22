<?php

namespace UAB\DateMaxMinModule;

use ExternalModules\AbstractExternalModule;
use Form;
use Piping;
use DateTime;

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
    public $geDateTag = "@GREATER-EQUAL-DATE";
    public $leDateTag = "@LESS-EQUAL-DATE";

    // Given $Proj->metadata[$field_name] return whether the field 
    // is a text field and has date validation applied
    function isDateTypeField(array $field): bool
    {
        $isTextField = $field['element_type'] == 'text';
        $hasDateValidation = in_array($field['element_validation_type'], Validate::$date_validations);
        return $isTextField && $hasDateValidation;
    }

    function containsGreaterEqualDateTag(?string $tags): bool
    {
        if (isset($tags) && strpos($tags, $this->geDateTag) !== false){
            return true;
        }
        return false;
    }

    function containsLessEqualDateTag(?string $tags): bool
    {
        if (isset($tags) && strpos($tags, $this->leDateTag) !== false){
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

    function redcap_data_entry_form ( int $project_id, string $record = NULL, string $instrument, int $event_id, int $group_id = NULL, int $repeat_instance = 1){

        /*
        global $Proj;

        $instrument_fields = $Proj->forms[$instrument]['fields'];


        $geFields = array();
        $leFields = array();



        foreach (array_keys($Proj->forms[$instrument]['fields']) as $field_name) {
            $field = $Proj->metadata[$field_name];
            

            if($this->isDateTypeField($field)){

                $action_tags = $field['misc'];

                if($this->containsGreaterEqualDateTag($action_tags)){
                    $geFields[$field_name] = $field;
                } 

                if($this->containsLessEqualDateTag($action_tags)){
                    $leFields[$field_name] = $field;
                }
                
            }
        }

        //echo "HI there!";

        $ge_date = $this->getMinMaxDate($geFields, $this->geDateTag, $record, $event_id, $repeat_instance, $instrument);
        $le_date = $this->getMinMaxDate($leFields, $this->leDateTag, $record, $event_id, $repeat_instance, $instrument);

        echo "greater than equal date: " . $ge_date . "<br>";

        echo "less than equal date: " . $le_date . "<br>";
        */
        


    }



    function getMinMaxDate($field_arr, $min_max_tag, $record, $event_id, $repeat_instance, $instrument){

        $date_arr = array();


        $action_tags = $field_arr['misc'];
        $new_action_tags = str_replace("'' ", "", $action_tags); // @IF action tag may add '' to the action tags string
        $date_pre = Form::getValueInQuotesActionTag($new_action_tags, $min_max_tag);
        //$getDatePre = Form::getValueInParenthesesActionTag($action_tags, $this->geDateTag);

        $date_pre = preg_replace('/\s+/', '', $date_pre);

        $all_date_fieds = explode(",", $date_pre);



        foreach ($all_date_fieds as $date_field) {
            //echo $date_field . "<br>";
            $dt = Piping::replaceVariablesInLabel($date_field, $record, $event_id,
                $repeat_instance, array(), false, null, false, "", 1, false, false, $instrument, null, true);

            //echo $ge_date . "<br>";

            if ($dt !== ''){
                $date_arr[] = $dt;
            }
        }
      
        




        if(count($date_arr) == 0){
            return "";
        }

        if($min_max_tag === $this->geDateTag){
            return max($date_arr);
        }

        if($min_max_tag === $this->leDateTag){
            return min($date_arr);
        }
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

        if($action == "min_max_date_validation"){

            global $Proj;
            $field_name = $payload['field_name'];

            $entered_dt_str = $payload['entered_date'];

            $entered_dt = DateTime::createFromFormat('m-d-Y', $entered_dt_str)->format('Y-m-d');
;
            $field = $Proj->metadata[$field_name];

            $ge_date = '';
            $le_date = '';

            $action_tags = $field['misc'];

            $record_id = $payload['record'];

            if($this->containsGreaterEqualDateTag($action_tags)){
                    //$geFields[$field_name] = $field;
                $ge_date = $this->getMinMaxDate($field, $this->geDateTag, $record_id, $event_id, $repeat_instance, $instrument);
            } 

            if($this->containsLessEqualDateTag($action_tags)){
                    //$leFields[$field_name] = $field;
                $le_date = $this->getMinMaxDate($field, $this->leDateTag, $record_id, $event_id, $repeat_instance, $instrument);
            }
/*
            if($this->isDateTypeField($field)){

                $action_tags = $field['misc'];

                if($this->containsGreaterEqualDateTag($action_tags)){
                    //$geFields[$field_name] = $field;
                    $ge_date = $this->getMinMaxDate($field, $this->geDateTag, $record, $event_id, $repeat_instance, $instrument);
                } 

                if($this->containsLessEqualDateTag($action_tags)){
                    //$leFields[$field_name] = $field;
                    $le_date = $this->getMinMaxDate($field, $this->leDateTag, $record, $event_id, $repeat_instance, $instrument);
                }
                
            }
*/
            $message = "";

            if($ge_date != '' && $entered_dt <= $ge_date){
                $ge_date_mdy = DateTime::createFromFormat('Y-m-d', $ge_date)->format('m-d-Y');
                $message .= "Date " . $entered_dt_str . " must be a later date after " . $ge_date_mdy . ".<br>";
            } 

            if($le_date != '' && $entered_dt >= $le_date ){
                $le_date_mdy = DateTime::createFromFormat('Y-m-d', $le_date)->format('m-d-Y');
                $message .= "Date " . $entered_dt_str . " must be a past date of " . $le_date_mdy . ".<br>";
            }
            $arr['ge_date'] = $ge_date;
            $arr['le_date'] = $le_date;
            $arr['record'] = $payload['record'];
            $arr['event_id'] = $event_id;
            $arr['repeat_instance'] = $repeat_instance;
            $arr['instrument'] = $instrument;
            $arr['returned_dt'] = $entered_dt;
            $arr["message"] = $message;


            return json_encode($arr);
        }
    }
}
