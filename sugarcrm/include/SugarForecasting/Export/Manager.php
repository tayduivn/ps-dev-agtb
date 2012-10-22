<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once('include/SugarForecasting/Export/AbstractExport.php');
require_once('include/SugarForecasting/Manager.php');
class SugarForecasting_Export_Manager extends SugarForecasting_Export_AbstractExport
{
    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct($args)
    {
        $this->isManager = true;
        parent::__construct($args);
    }


    public function process()
    {
        global $current_user;

        // base file and class name
        $file = 'include/SugarForecasting/Manager.php';
        $klass = 'SugarForecasting_Manager';

        // check for a custom file exists
        $include_file = get_custom_file_if_exists($file);

        // if a custom file exists then we need to rename the class name to be Custom_
        if($include_file != $file) {
            $klass = "Custom_" . $klass;
        }

        // include the class in since we don't have a auto loader
        require_once($include_file);
        // create the lass

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($this->args);
        $data = $obj->process();

        $fields_array = array(
            'amount'=>'amount',
            'quota'=>'quota',
            'quota_id'=>'quota_id',
            'best_case'=>'best_case',
            'likely_case'=>'likely_case',
            'worst_case'=>'worst_case',
            'label'=>'label',
            'date_modified'=>'date_modified',
            'best_adjusted'=>'best_adjusted',
            'likely_adjusted'=>'likely_adjusted',
            'worst_adjusted'=>'worst_adjusted',
            'forecast'=>'forecast',
            'forecast_id'=>'forecast_id',
            'worksheet_id'=>'worksheet_id',
            'commit_stage'=>'commit_stage',
            'currency_id'=>'currency_id',
            'base_rate'=>'base_rate',
            'show_opps'=>'show_opps',
            'timeperiod_id'=>'timeperiod_id',
            'id'=>'id',
            'label'=>'label',
            'name'=>'name',
            'user_id'=>'user_id',
            'version'=>'version'
        );

        $seed = BeanFactory::getBean('ForecastManagerWorksheets');

        require_once('include/export_utils.php');

        //set up labels to be used for the header row
        $field_labels = array();
        foreach($fields_array as $key=>$label)
        {
             $field_labels[$key] = translateForExport($label, $seed);
        }

        // setup the "header" line with proper delimiters
        $content = "\"".implode("\"".getDelimiter()."\"", array_values($field_labels))."\"\r\n";

        if(!empty($data))
        {
            //process retrieved record
            //BEGIN SUGARCRM flav=pro ONLY
            $isAdminUser = is_admin($current_user);
            //END SUGARCRM flav=pro ONLY

            foreach($data as $val)
            {
                $content .= getExportContentForRow($val, $seed, $isAdminUser, $fields_array);
            }
        }

        return $content;
    }

}