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

	public function __construct()
	{
	}

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
        //load defaults and settings
        $this->loadArgs($args);
        //get the quota data for user
        $quota = new Quota();
        $quotaData = $quota->getRollupQuota($this->timeperiod_id, $this->user_id);

		$progressData = array(
            "quota_amount"      => isset($quotaData["amount"]) ? $quotaData["amount"] : 0
		);

		return $progressData;
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
        //load defaults and settings
        $this->loadArgs($args);

        //create opportunity to use to build queries
        $this->opportunity = new Opportunity();

        //get data
		$progressData = array(
            "closed_amount"     => $this->getClosedAmount($this->user_id, $this->timeperiod_id, $this->sales_stage_won),
            "opportunities"     => $this->getPipelineOpportunityCount($this->user_id, $this->timeperiod_id, $this->sales_stage_won, $this->sales_stage_lost)
		);

		return $progressData;
	}

    /**
     * get settings and load default arguments in the case that they aren't passed into the api endpoint
     *
     * @param $args
     */
    public function loadArgs($args) {
        $admin = new Administration();
        $admin->retrieveSettings();

        //check for timeperiod and userid from args passed in, default them if they weren't passed in
        $this->user_id = isset($args["user_id"]) ? $args["user_id"] : $GLOBALS["current_user"]->id;
        $this->timeperiod_id = isset( $args["timeperiod_id"]) ? $args["timeperiod_id"] : TimePeriod::getCurrentId();

        // decode and json decode the settings from teh administration to set the sales stages for closed won and closed lost
        $this->sales_stage_won = json_decode(html_entity_decode($admin->settings["base_sales_stage_won"]));
        $this->sales_stage_lost = json_decode(html_entity_decode($admin->settings["base_sales_stage_lost"]));
    }

    /**
     * retreives the number of opportunities set to be used in this forecast period, excludes only the closed stages
     *
     * @param null $user_id
     * @param null $timeperiod_id
     * @param bool $should_rollup
     * @return mixed
     */
    protected function getPipelineOpportunityCount( $user_id = NULL, $timeperiod_id = NULL, $excluded_sales_stages_won, $excluded_sales_stages_lost  )
   	{
        //set user ids and timeperiods
        $where = " users.reports_to_id = " . $GLOBALS['db']->quoted($user_id);
   		$where .= " AND opportunities.timeperiod_id = " . $GLOBALS['db']->quoted($timeperiod_id);


        //per requirements, exclude the sales stages won
        if(count($excluded_sales_stages_won)) {
           foreach($excluded_sales_stages_won as $exclusion)
           {
               $where .= " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted($exclusion);
           }
        }

        //per the requirements, exclude the sales stages for closed lost
        if(count($excluded_sales_stages_lost)) {
           foreach($excluded_sales_stages_lost as $exclusion)
           {
               $where .= " AND opportunities.sales_stage != " . $GLOBALS['db']->quoted($exclusion);
           }
        }

        // no deleted opportunities
        $where .= " AND opportunities.deleted = 0";

        //build the query
   		$query = $this->opportunity->create_list_query(NULL, $where);
   		$query = $this->opportunity->create_list_count_query($query);

   		$result = $GLOBALS['db']->query($query);
   		$row = $GLOBALS['db']->fetchByAssoc($result);
   		$opportunitiesCount = $row['c'];

   		return $opportunitiesCount;
   	}


    /**
     * retrieves the amount of closed won opportunities
     *
   	 * @param null $user_id
   	 * @param null $timeperiod_id
   	 *
   	 * @return int
   	 */
   	public function getClosedAmount( $user_id = NULL, $timeperiod_id = NULL, $sales_stage_won=array() )
   	{
   		$amountSum = 0;

        //set user ids and timeperiods
        $where = " users.reports_to_id = " . $GLOBALS['db']->quoted($user_id);
        $where .= " AND opportunities.timeperiod_id = " . $GLOBALS['db']->quoted($timeperiod_id);

        // no deleted opportunities
        $where .= " AND opportunities.deleted = 0";

        //pre requirements, include only closed won opportunities
        if(count($sales_stage_won)) {
           $where .= " AND opportunities.sales_stage in ( '";
           $where .= join("','", $sales_stage_won) . "')";
        }

        //build and execute query
   		$query  = $this->opportunity->create_list_query(NULL, $where);
   		$result = $GLOBALS['db']->query($query);

   		while ( $row = $GLOBALS['db']->fetchByAssoc($result) ) {
   			$amountSum += $row["amount"];
   		}

   		return $amountSum;
   	}
}
