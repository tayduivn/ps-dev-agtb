<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/api/ModuleApi.php');

class ForecastsWorksheetApi extends ModuleApi {

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        //Extend with test method
        $parentApi= array (
            'forecastWorksheet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastWorksheets'),
                'pathVars' => array('',''),
                'method' => 'forecastWorksheet',
                'shortHelp' => 'Returns a collection of ForecastWorksheet models',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#forecastWorksheet',
            ),
            'forecastWorksheetSave' => array(
                'reqType' => 'PUT',
                'path' => array('ForecastWorksheets','?'),
                'pathVars' => array('module','record'),
                'method' => 'forecastWorksheetSave',
                'shortHelp' => 'Updates a ForecastWorksheet model',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetApi.html#forecastWorksheet',
            )
        );
        return $parentApi;
    }


    /**
     * This method handles the /ForecastsWorksheet REST endpoint and returns an Array of worksheet data Array entries
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return Array of worksheet data entries
     * @throws SugarApiExceptionNotAuthorized
     */
    public function forecastWorksheet($api, $args) {
        // Load up a seed bean
        require_once('modules/Forecasts/ForecastWorksheet.php');
        $seed = new ForecastWorksheet();

        if (!$seed->ACLAccess('list') ) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }
		global $app_list_strings,$current_language, $current_user;
		
		$timeperiod_id =  isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $user_id = isset($args['user_id']) ? $args['user_id'] : $current_user->id;
		
        $sql = "select o.id, " .
        	          "o.amount, " .
        	          "o.date_closed, " .
        	          "o.probability, " .
        	          "o.commit_stage, " .
        	          "o.sales_stage, " .
        	          "o.timeperiod_id, " .
        	          "o.currency_id, " .
        	          "o.name, " .
        	          "o.best_case, " .
        	          "o.worst_case, " .
        	          "o.forecast, " .
        	          "o.assigned_user_id, " .
        	          "w.id worksheet_id, " .
        	          "w.user_id w_user_id, " .
        	          "w.forecast w_forecast, " .
        	          "w.best_case w_best_case, " .
        	          "w.likely_case w_likely_case, " .
        	          "w.worst_case w_worst_case, " .
        	          "w.forecast_type w_forecast_type, " .
        	          "w.related_id w_related_id, " .
        	          "w.version w_version, " .
        	          "w.commit_stage w_commit_stage, " .
        	          "w.op_probability w_probability, " .
        	          "w.currency_id w_currency_id " .
        	          "from opportunities o " .
        	          "left join worksheet w " .
        	          	"on o.id = w.related_id ";
        if($user_id == $current_user->id)
        {
        	$sql .=    	"and w.date_modified = (select max(date_modified) from worksheet w2 " .
        	          							"where w2.user_id = o.assigned_user_id and related_id = o.id " .
        	          								"and timeperiod_id = '" . $timeperiod_id . "') ";
        }
        else
        {
        	$sql .= 	"and w.version = 1 ";
        }
        	          
		$sql .=		  "where o.timeperiod_id = '" . $timeperiod_id . "' " .
        	          	"and o.assigned_user_id = '" . $user_id ."'";
       
        $result = $GLOBALS['db']->query($sql);

        $returnData = array();

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data = "";
            $data->id = $row["id"];
            $data->date_closed = $row["date_closed"];
            $data->sales_stage = $row["sales_stage"];
            $data->assigned_user_id = $row["assigned_user_id"];
            $data->amount = $row["amount"];
            $data->worksheet_id = "";
            $data->name = $row["name"];
            
            if(isset($row["worksheet_id"]))
            {
            	$data->worksheet_id = $row["worksheet_id"];
            	$data->forecast = $row["w_forecast"];
            	$data->best_case = $row["w_best_case"];
            	$data->worst_case = $row["w_worst_case"];
            	$data->amount = $row["w_likely_case"];
            	$data->commit_stage = $row["w_commit_stage"];
            	$data->probability = $row["w_probability"];
            	$data->version = $row["w_version"];
            }
            else
            {
            	$data->forecast = $row["forecast"];
            	$data->best_case = $row["best_case"];
            	$data->worst_case = $row["worst_case"];
            	$data->commit_stage = $row["commit_stage"];
            	$data->probability = $row["probability"];
            }
            $returnData[] = $data;
            
        }             
            	       
        return $returnData;
    }

    /**
     * This method handles saving data for the /ForecastsWorksheet REST endpoint
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return Array of worksheet data entries
     * @throws SugarApiExceptionNotAuthorized
     */
    public function forecastWorksheetSave($api, $args) {
        require_once('modules/Forecasts/ForecastWorksheet.php');
        require_once('include/SugarFields/SugarFieldHandler.php');
        $seed = new ForecastWorksheet();
        $seed->loadFromRow($args);
        $sfh = new SugarFieldHandler();

        foreach ($seed->field_defs as $properties)
        {
            $fieldName = $properties['name'];

            if(!isset($args[$fieldName]))
            {
               continue;
            }

            //BEGIN SUGARCRM flav=pro ONLY
            if (!$seed->ACLFieldAccess($fieldName,'save') ) {
                // No write access to this field, but they tried to edit it
                throw new SugarApiExceptionNotAuthorized('Not allowed to edit field '.$fieldName.' in module: '.$args['module']);
            }
            //END SUGARCRM flav=pro ONLY

            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);

            if($field != null)
            {
               $field->save($seed, $args, $fieldName, $properties);
            }
        }
		$seed->setWorksheetArgs($args);
        $seed->save();
        return $seed->id;
    }
    
    /**
     * This function gets the worksheet id related to opportunities
     * @param string oppId Opportunity ID
     */
    protected function getRelatedWorksheetID($oppId)
    {
        //getting data from worksheet table for reportees
        $sql = "SELECT w.id worksheet_id
                            FROM worksheet w
                            WHERE w.related_id = '{$oppId}' AND w.forecast_type = 'Direct'";
		
        $result = $GLOBALS['db']->query($sql);

        $data = '';

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data = $row['worksheet_id'];
        }             

        return $data;
    }
}
