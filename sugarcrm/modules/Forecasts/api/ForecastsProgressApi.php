<?php
if ( !defined('sugarEntry') || !sugarEntry ) {
	die('Not A Valid Entry Point');
}
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

require_once('modules/Forecasts/ForecastOpportunities.php');

class ForecastsProgressApi extends ModuleApi
{

    /**
     * uuid for the selected user
     *
     * @var string
     */
	protected $user_id;
    /**
     * uuid for the current/selected timeperiod
     *
     * @var string
     */
	protected $timeperiod_id;
    /**
     * Opportunity Bean used to create the opportunity queries
     *
     * @var Opportunity
     */
	protected $opportunity;
    /**
     * array of sales stages to denote as closed('lost')
     *
     * @var array
     */
    protected $sales_stage_lost = Array();
    /**
     * array of sales stages to denote as closed('won')
     *
     * @var array
     */
    protected $sales_stage_won = Array();

    /**
     * Rest Api Registration Method
     *
     * @return array
     */
    public function registerApiRest()
	{
		$parentApi = parent::registerApiRest();

		$parentApi = array(
			'progressRep' => array(
				'reqType'   => 'GET',
				'path'      => array('Forecasts', 'progressRep'),
				'pathVars'  => array('', ''),
				'method'    => 'progressRep',
				'shortHelp' => 'Progress Rep data',
                'longHelp' => 'modules/Forecasts/api/help/ForecastProgressApi.html#progressRep',
			),
            'progressManager' => array(
                'reqType'   => 'GET',
                'path'      => array('Forecasts', 'progressManager'),
                'pathVars'  => array('', ''),
                'method'    => 'progressManager',
                'shortHelp' => 'Progress Manager data',
                 'longHelp' => 'modules/Forecasts/api/help/ForecastProgressApi.html#progressRep',
         	)
        );
		return $parentApi;
	}

    /**
     * loads data and passes back an array to communicate data that may be missing.  The array is the same
     *
     * @param $api
     * @param $args
     * @return array
     */
	public function progressRep( $api, $args )
	{
        $args['user_id'] = isset($args["user_id"]) ? $args["user_id"] : $GLOBALS["current_user"]->id;
        $args['timeperiod_id'] = isset( $args["timeperiod_id"]) ? $args["timeperiod_id"] : TimePeriod::getCurrentId();

        // base file and class name
        $file = 'include/SugarForecasting/Progress/Individual.php';
        $klass = 'SugarForecasting_Progress_Individual';

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
        $obj = new $klass($args);
        return $obj->process();
	}

    /**
     * loads data and passes back an array to communicate data that may be missing.  The array is the same
     *
     * @param $api
     * @param $args
     * @return array
     */
	public function progressManager( $api, $args )
	{
        $args['user_id'] = isset($args["user_id"]) ? $args["user_id"] : $GLOBALS["current_user"]->id;
        $args['timeperiod_id'] = isset( $args["timeperiod_id"]) ? $args["timeperiod_id"] : TimePeriod::getCurrentId();
        // base file and class name
        $file = 'include/SugarForecasting/Progress/Manager.php';
        $klass = 'SugarForecasting_Progress_Manager';

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
        $obj = new $klass($args);
        return $obj->process();
	}
}
