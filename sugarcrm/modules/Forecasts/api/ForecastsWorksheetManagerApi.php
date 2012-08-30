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
require_once('modules/Forecasts/api/ForecastsChartApi.php');

class ForecastsWorksheetManagerApi extends ForecastsChartApi {

    protected $user_id;
    protected $timeperiod_id;

    public function __construct()
    {

    }

    /**
     * Set the user_id for the class
     *
     * @param $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Set the time period for the class
     *
     * @param $timeperiod_id
     */
    public function setTimePeriodId($timeperiod_id)
    {
        $this->timeperiod_id = $timeperiod_id;
    }

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'forecastManagerWorksheet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastManagerWorksheets'),
                'pathVars' => array('',''),
                'method' => 'forecastManagerWorksheet',
                'shortHelp' => 'Returns a collection of ForecastManagerWorksheet models',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetManagerApi.html#forecastWorksheetManager',
            ),
            'forecastManagerWorksheetSave' => array(
                'reqType' => 'PUT',
                'path' => array('ForecastManagerWorksheets','?'),
                'pathVars' => array('module','record'),
                'method' => 'forecastManagerWorksheetSave',
                'shortHelp' => 'Update a ForecastManagerWorksheet model',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastWorksheetManagerApi.html#forecastWorksheetManagerSave',
            )
        );
        return $parentApi;
    }

    /**
     * This method returns the result for a sales rep view/manager's opportunities view
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function worksheetManager($api, $args)
    {
        require_once('modules/Reports/Report.php');
        require_once('modules/Forecasts/data/ChartAndWorksheetManager.php');

        global $current_user, $mod_strings, $app_list_strings, $app_strings, $current_language, $locale;
		$current_module_strings = return_module_language($current_language, 'Forecasts');

        if(isset($args['user_id']) && User::isManager($args['user_id'])) {
            /** @var $user User */
            $user = BeanFactory::getBean('Users', $args['user_id']);
        } elseif(!isset($args['user_id']) && User::isManager($current_user->id)){
            /** @var $user User */
            $user = $current_user;
        } else {
            return array();
        }

        $app_list_strings = return_app_list_strings_language($current_language);

        $this->timeperiod_id =  isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $this->user_id = isset($args['user_id']) ? $args['user_id'] : $user->id;

        //populate output with default data
        $default_data = array("amount" => 0,
                              "quota" => 0,
                              "quota_id" => '',
                              "best_case" => 0,
                              "likely_case" => 0,
                              "worst_case" => 0,
                              "best_adjusted" => 0,
                              "likely_adjusted" => 0,
                              "worst_adjusted" => 0,
                              "forecast" => 0,
                              "forecast_id" => '',
                              "worksheet_id" => '',
                              "show_opps" => false,
                              "timeperiod_id" => $this->timeperiod_id,
                              "id" => ""
                            );



		if($current_user->id == $user->id || (isset($args["user_id"]) && ($args["user_id"] == $user->id))){
        	$default_data["name"] = string_format($current_module_strings['LBL_MY_OPPORTUNITIES'], array($locale->getLocaleFormattedName($user->first_name, $user->last_name)));
            $default_data["show_opps"] = true;
        } else {
			$default_data["name"] = $locale->getLocaleFormattedName($user->first_name, $user->last_name);
		}
		
        $default_data["user_id"] = $user->id;
        $default_data["id"] = $user->id;
        $data[$user->user_name] = $default_data;

        require_once("modules/Forecasts/Common.php");
        $common = new Common();
        $common->retrieve_direct_downline($this->user_id);

        foreach($common->my_direct_downline as $reportee_id) {
            /** @var $reportee User */
            $reportee = BeanFactory::getBean('Users', $reportee_id);
            $default_data['id'] = $reportee_id;
            $default_data['name'] = $locale->getLocaleFormattedName($reportee->first_name, $reportee->last_name);
            $default_data['user_id'] = $reportee_id;
            $default_data["show_opps"] = User::isManager($reportee_id) ? false : true;
            $data[$reportee->user_name] = $default_data;
        }

        $data_grid = array_replace_recursive($data, $this->getReportData($args));

        $quota = $this->getQuota();
        $forecast = $this->getForecastValues();
        $worksheet = $this->getWorksheetBestLikelyAdjusted();
        $data_grid = array_replace_recursive($data_grid, $quota, $forecast, $worksheet);

        //bug 54619:
        //Best/Likely (Adjusted) numbers by default should be the same as best/likely numbers
        foreach($data_grid as $rep => $val)
        {
            // we dont have a forecast yet, set the amount to 0
            if(empty($val['forecast_id'])) {
                $data_grid[$rep]['amount'] = 0;
            } else if($val['user_id'] != $this->user_id && $val['show_opps'] == false) {
                // we need to get their total amount including sales reps.
                // first get the reportees that have a forecast submitted for this time period
                $manager_reportees_forecast = $common->getReporteesWithForecasts($val['user_id'], $this->timeperiod_id);
                // second, we need to get the data all the reporting users
                $manager_data = $this->getReportData($args, $val['user_id']);
                // third we only process the users that actually have a committed forecast;
                foreach($manager_data as $name => $m_data) {
                    if(in_array($name, $manager_reportees_forecast)) {
                        // add it to the managers amount
                        $data_grid[$rep]['amount'] += $m_data['amount'];
                    }
                }
            }

            $data_grid[$rep]['best_adjusted'] = empty($val['best_adjusted']) ? $val['best_case'] : $val['best_adjusted'];
            $data_grid[$rep]['likely_adjusted'] = empty($val['likely_adjusted']) ? $val['likely_case'] : $val['likely_adjusted'];
            $data_grid[$rep]['worst_adjusted'] = empty($val['worst_adjusted']) ? $val['worst_case'] : $val['worst_adjusted'];
            // set the order by the key to make testing easier;
            ksort($data_grid[$rep]);
        }
        return array_values($data_grid);
    }

    /**
     * Get the report data with filters.
     *
     * @param array $args
     * @param null|string $user_id
     * @return array
     */
    protected function getReportData($args, $user_id = null)
    {
        if(empty($user_id)) {
            $user_id = $this->user_id;
        }
        $mgr = new ChartAndWorksheetManager();
        $report_defs = $mgr->getWorksheetDefinition('manager', 'opportunities');

        $testFilters = array(
            'timeperiod_id' => array('$is' => $this->timeperiod_id),
            'assigned_user_link' => array('id' => array('$or' => array('$is' => $user_id, '$reports' => $user_id))),
            'forecast' => array('$is' => 1) // TODO: fix for when buckets is enabled
        );

        require_once('include/SugarParsers/Filter.php');
        require_once("include/SugarParsers/Converter/Report.php");
        require_once("include/SugarCharts/ReportBuilder.php");

        // create the a report builder instance
        $rb = new ReportBuilder("Opportunities");
        // load the default report into the report builder
        $rb->setDefaultReport($report_defs[2]);

        // parse any filters from above
        $filter = new SugarParsers_Filter(new Opportunity());
        $filter->parse($testFilters);
        $converter = new SugarParsers_Converter_Report($rb);
        $reportFilters = $filter->convert($converter);
        // add the filter to the report builder

        $rb->addFilter($reportFilters);

        // create the json for the reporting engine to use
        $chart_contents = $rb->toJson();

        $report = new Report($chart_contents);

        return $mgr->getWorksheetGridData('manager', $report);
    }

    protected function getQuota()
    {
        //getting quotas from quotas table
        $quota_query = "SELECT u.user_name user_name, q.amount quota, q.id quota_id " .
					   "FROM quotas q " .
					   "INNER JOIN users u " .
					   "ON q.user_id = u.id " .
					   "WHERE q.timeperiod_id = '{$this->timeperiod_id}' " .
					   "AND ((u.id = '{$this->user_id}' and q.quota_type = 'Direct') " . 
						    "OR (u.reports_to_id = '{$this->user_id}' and q.quota_type = 'Rollup'))";
		
        $result = $GLOBALS['db']->query($quota_query);
        $data = array();

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['quota_id'] = $row['quota_id'];
            $data[$row['user_name']]['quota'] = $row['quota'];
        }

        return $data;
    }


    /**
     * This function returns the best, likely and worst case values from the forecasts table for the manager
     * associated with the user_id class variable.  It is a helper function used by the manager worksheet api
     * to return forecast related information.
     *
     * @return array Array of entries with deltas best_case, likely_case, worst_case, id, date_modified and forecast_id
     */
    public function getForecastValues()
    {
    	//Partially optimized.. Don't delete
    	/*$data = array();
        	
        $sql = "select u.user_name, f.id, f.best_case, f.likely_case, f.worst_case, f.forecast_type, f.date_modified " .
        		"from forecasts f " .
        		"inner join users u " .
        			"on f.user_id = u.id " .
        				"and (u.reports_to_id = '" . $this->user_id . "' " .
        					 "or u.id = '" . $this->user_id . "') " .
        		"where f.timeperiod_id = '" . $this->timeperiod_id . "' " .
        			"and ((f.user_id = '" . $this->user_id . "' and f.forecast_type = 'Direct') " .
        				 "or (f.user_id <> '" . $this->user_id . "' and f.forecast_type = 'Rollup'))" .
        			"and f.deleted = 0 " .
        			"and f.date_modified = (select max(date_modified) from forecasts where user_id = u.id and timeperiod_id = '" . $this->timeperiod_id . "')";
        $result = $GLOBALS['db']->query($sql);

		while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
		{
            $data[$row['user_name']]['best_case'] = $row['best_case'];
            $data[$row['user_name']]['likely_case'] = $row['likely_case'];
            $data[$row['user_name']]['worst_case'] = $row['worst_case'];
            $data[$row['user_name']]['forecast_id'] = $row['id'];
            $data[$row['user_name']]['date_modified'] = $row['date_modified'];
        }
            
        return $data;*/
        
        $query = "SELECT id, user_name FROM users WHERE reports_to_id = '{$this->user_id}' AND deleted = 0";
        $db = DBManagerFactory::getInstance();
        $result = $db->query($query);

        $ids = array();
        while($row=$db->fetchByAssoc($result))
        {
            $ids[$row['id']] = $row['user_name'];
        }

        //Add the manager's data as well
        /** @var $user User */
        $user = BeanFactory::getBean('Users', $this->user_id);
        $ids[$this->user_id] = $user->user_name;

        $data = array();

        foreach($ids as $id=>$user_name)
        {
            // if the reportee is the manager, we need to get the roll up amount instead of the direct amount
            $forecast_type = (User::isManager($id) && $id != $this->user_id) ? 'ROLLUP' : 'DIRECT';
            $forecast_query = "SELECT id, best_case, likely_case, worst_case, date_modified
                                FROM forecasts
                                WHERE timeperiod_id = '{$this->timeperiod_id}'
                                    AND forecast_type = '" . $forecast_type . "'
                                    AND user_id = '" . $id .  "'
                                    AND deleted = 0 ORDER BY date_modified DESC";
            $result = $db->limitQuery($forecast_query, 0, 1);

            while($row=$db->fetchByAssoc($result)) {
                $data[$user_name]['best_case'] = $row['best_case'];
                $data[$user_name]['likely_case'] = $row['likely_case'];
                $data[$user_name]['worst_case'] = $row['worst_case'];
                $data[$user_name]['forecast_id'] = $row['id'];
                $data[$user_name]['date_modified'] = $row['date_modified'];
            }
        }

        return $data;
        
    }

    /**
     * Get the Worksheet Adjusted Values
     *
     * @return array
     */
    public function getWorksheetBestLikelyAdjusted()
    {
    	global $current_user;
        //getting data from worksheet table for reportees
		$reportees_query = "SELECT u2.user_name, " .
						   "w.id worksheet_id, " .
						   "w.forecast, " .
						   "w.best_case best_adjusted, " .
						   "w.likely_case likely_adjusted, " .
						   "w.worst_case worst_adjusted, " .
						   "w.forecast_type, " .
						   "w.related_id, " .
						   "w.version, " .
						   "w.quota " .
						   "from users u " .
						   "inner join users u2 " .
						   		"on u.id = u2.reports_to_id " .
						   		"or u.id = u2.id " .
						   "inner join worksheet w " .
						   		"on w.user_id = u.id " .
						   		"and w.timeperiod_id = '" . $this->timeperiod_id . "'" .
						   		"and ((w.related_id = u.id and u2.id = u.id)" .
						   			 "or(w.related_id = u2.id)) " .
						   "where u.id = '" . $this->user_id . "' " .
						   		"and w.deleted = 0 ";
						   		
						   		
		if($this->user_id == $current_user->id)
		{
			$reportees_query .=	"and w.date_modified = (select max(date_modified) from worksheet " .
						   								"where user_id = u.id and related_id = u2.id " .
						   										"and timeperiod_id = '" . $this->timeperiod_id . "')";
		}
		else
		{
			$reportees_query .= "and w.version = 1";
		}
		$GLOBALS['log']->fatal("In api user:    " . $this->user_id);
		$GLOBALS['log']->fatal("In api current: " . $current_user->id);
        $result = $GLOBALS['db']->query($reportees_query);
        $data = array();
	
        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['worksheet_id'] = $row['worksheet_id'];
            $data[$row['user_name']]['best_adjusted'] = $row['best_adjusted'];
            $data[$row['user_name']]['likely_adjusted'] = $row['likely_adjusted'];
            $data[$row['user_name']]['worst_adjusted'] = $row['worst_adjusted'];
            $data[$row['user_name']]['forecast'] = $row['forecast'];
            $data[$row['user_name']]['version'] = $row['version'];
            if($row['version'] == 0)
            {
            	$data[$row['user_name']]['quota'] = $row['quota'];	
            }
            
        }             
		
        return $data;
    }


    public function forecastManagerWorksheet($api, $args) {
        // Load up a seed bean
        require_once('modules/Forecasts/ForecastManagerWorksheet.php');
        $seed = new ForecastManagerWorksheet();

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }

        return $this->worksheetManager($api, $args);
    }


    public function forecastManagerWorksheetSave($api, $args) {
        require_once('modules/Forecasts/ForecastManagerWorksheet.php');
        require_once('include/SugarFields/SugarFieldHandler.php');
        $seed = new ForecastManagerWorksheet();
        $seed->loadFromRow($args);
        $sfh = new SugarFieldHandler();

        foreach ($seed->field_defs as $properties)
        {
            $fieldName = $properties['name'];

            if (!isset($args[$fieldName])) {
                continue;
            }

            //BEGIN SUGARCRM flav=pro ONLY
            if (!$seed->ACLFieldAccess($fieldName, 'save')) {
                // No write access to this field, but they tried to edit it
                throw new SugarApiExceptionNotAuthorized('Not allowed to edit field ' . $fieldName . ' in module: ' . $args['module']);
            }
            //END SUGARCRM flav=pro ONLY

            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);

            if ($field != null) {
                $field->save($seed, $args, $fieldName, $properties);
            }
        }

        $seed->setWorksheetArgs($args);
        $seed->save();
        return $seed->id;
    }

}
