<?php

// This class is used for the Manager Views
require_once('include/SugarForecasting/AbstractForecast.php');
require_once('include/SugarForecasting/Exception.php');
class SugarForecasting_Manager extends SugarForecasting_AbstractForecast
{

    /**
     * Where we store the data we want to use
     *
     * @var array
     */
    protected $dataArray = array();

    /**
     * Default Data Array To Start With
     *
     * @var array
     */
    protected $defaultData = array("amount" => 0,
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
                              "timeperiod_id" => "",
                              "id" => ""
                            );


    /**
     * Class Constructor
     * @param array $args       Service Arguments
     */
    public function __construct($args)
    {
        // set the isManager Flag just incase we need it
        $this->isManager = true;

        parent::__construct($args);

        // set the default data timeperiod to the set timeperiod
        $this->defaultData['timeperiod_id'] = $this->getArg('timeperiod_id');
    }

    /**
     * Run all the tasks we need to process get the data back
     *
     * @return array|string
     */
    public function process()
    {
        try {
            $this->loadUsers();
        } catch (SugarForecasting_Exception $sfe) {
            return "";
        }

        $this->loadUsersAmount();
        $this->loadUsersQuota();
        $this->loadForecastValues();
        $this->loadWorksheetAdjustedValues();
        $this->loadManagerAmounts();

        return array_values($this->dataArray);
    }

    /**
     * Return the data array
     *
     * @return array
     */
    public function getDataArray()
    {
        return $this->dataArray;
    }

    /**
     * Load the Users for the passed in user in the $arguments
     *
     * @throws SugarForecasting_Exception
     */
    protected function loadUsers()
    {
        global $current_user, $mod_strings, $locale;

        if(empty($mod_strings)) {
            global $current_language;
            $mod_strings = return_module_language($current_language, "Forecasts");
        }

        $args = $this->getArgs();

        if(isset($args['user_id']) && User::isManager($args['user_id'])) {
            /** @var $user User */
            $user = BeanFactory::getBean('Users', $args['user_id']);
        } elseif(!isset($args['user_id']) && User::isManager($current_user->id)){
            /** @var $user User */
            $user = $current_user;
            $this->setArg('user_id', $user->id);
        } else {
            throw new SugarForecasting_Exception('User Is Not Manager');
        }

        $user_id = $this->getArg('user_id');

        $reportees = $this->getUserReportees($this->getArg('user_id'));

        $data = array();

        foreach($reportees as $reportee_id=>$reportee_username) {
            /** @var $reportee User */
            $reportee = BeanFactory::getBean('Users', $reportee_id);
            $default_data = $this->defaultData;
            $default_data['id'] = $reportee_id;
            if($reportee_id == $user_id) {
                // we have the owner
                $default_data["name"] = string_format($mod_strings['LBL_MY_OPPORTUNITIES'], array($locale->getLocaleFormattedName($user->first_name, $user->last_name)));
                $default_data["show_opps"] = true;
            } else {
                $default_data['name'] = $locale->getLocaleFormattedName($reportee->first_name, $reportee->last_name);
                $default_data["show_opps"] = User::isManager($reportee_id) ? false : true;
            }
            $default_data['user_id'] = $reportee_id;
            $data[$reportee->user_name] = $default_data;
        }

        $this->dataArray = $data;
    }

    /**
     * Load the base amounts for the users in the dataArray
     */
    protected function loadUsersAmount()
    {
        $amounts = $this->getUserAmounts();

        $this->dataArray = array_replace_recursive($this->dataArray, $amounts);
    }

    /**
     * Load the Quota's for the users in the dataArray
     */
    protected function loadUsersQuota()
    {
        //getting quotas from quotas table
        $db = DBManagerFactory::getInstance();
        $quota_query = "SELECT u.user_name user_name, q.amount quota, q.id quota_id " .
					   "FROM quotas q " .
					   "INNER JOIN users u " .
					   "ON q.user_id = u.id " .
					   "WHERE q.timeperiod_id = '{$this->getArg('timeperiod_id')}' " .
					   "AND ((u.id = '{$this->getArg('user_id')}' and q.quota_type = 'Direct') " .
						    "OR (u.reports_to_id = '{$this->getArg('user_id')}' and q.quota_type = 'Rollup'))";

        $result = $db->query($quota_query);
        $data = array();

        while(($row=$db->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['quota_id'] = $row['quota_id'];
            $data[$row['user_name']]['quota'] = $row['quota'];
        }

        $this->dataArray = array_replace_recursive($this->dataArray, $data);
    }

    /**
     * Get the Worksheet Adjusted Values
     */
    public function loadWorksheetAdjustedValues()
    {
        $args = $this->getArgs();

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
						   		"and w.timeperiod_id = '" . $args['timeperiod_id'] . "'" .
						   		"and ((w.related_id = u.id and u2.id = u.id)" .
						   			 "or(w.related_id = u2.id)) " .
						   "where u.id = '" . $args['user_id'] . "' " .
						   		"and w.deleted = 0 ";


		if($args['user_id'] == $current_user->id)
		{
			$reportees_query .=	"and w.date_modified = (select max(date_modified) from worksheet " .
						   								"where user_id = u.id and related_id = u2.id " .
						   										"and timeperiod_id = '" . $args['timeperiod_id'] . "')";
		}
		else
		{
			$reportees_query .= "and w.version = 1";
		}

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

        $this->dataArray = array_replace_recursive($this->dataArray, $data);
    }

    /**
     * This function returns the best, likely and worst case values from the forecasts table for the manager
     * associated with the user_id class variable.  It is a helper function used by the manager worksheet api
     * to return forecast related information.
     */
    protected function loadForecastValues()
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

        $args = $this->getArgs();

        $query = "SELECT id, user_name FROM users WHERE reports_to_id = '" . $args['user_id'] . "' AND deleted = 0";
        $db = DBManagerFactory::getInstance();
        $result = $db->query($query);

        $ids = array();
        while($row=$db->fetchByAssoc($result))
        {
            $ids[$row['id']] = $row['user_name'];
        }

        //Add the manager's data as well
        /** @var $user User */
        $user = BeanFactory::getBean('Users', $args['user_id']);
        $ids[$args['user_id']] = $user->user_name;

        $data = array();

        foreach($ids as $id=>$user_name)
        {
            // if the reportee is the manager, we need to get the roll up amount instead of the direct amount
            $forecast_type = (User::isManager($id) && $id != $args['user_id']) ? 'ROLLUP' : 'DIRECT';
            $forecast_query = "SELECT id, best_case, likely_case, worst_case, date_modified
                                FROM forecasts
                                WHERE timeperiod_id = '" . $args['timeperiod_id'] . "'
                                    AND forecast_type = '" . $forecast_type . "'
                                    AND user_id = '" . $id .  "'
                                    AND deleted = 0 ORDER BY date_modified DESC";
            $result = $db->limitQuery($forecast_query, 0, 1);

            while($row=$db->fetchByAssoc($result)) {
                $data[$user_name]['best_case'] = $row['best_case'];
                // make sure that adjusted is not equal to zero, this might be over written by the loadWorksheetAdjustedValues call
                $data[$user_name]['best_adjusted'] = $row['best_case'];
                $data[$user_name]['likely_case'] = $row['likely_case'];
                // make sure that adjusted is not equal to zero, this might be over written by the loadWorksheetAdjustedValues call
                $data[$user_name]['likely_adjusted'] = $row['likely_case'];
                $data[$user_name]['worst_case'] = $row['worst_case'];
                // make sure that adjusted is not equal to zero, this might be over written by the loadWorksheetAdjustedValues call
                $data[$user_name]['worst_adjusted'] = $row['worst_case'];
                $data[$user_name]['forecast_id'] = $row['id'];
                $data[$user_name]['date_modified'] = $row['date_modified'];
            }
        }

        $this->dataArray = array_replace_recursive($this->dataArray, $data);
    }

    /**
     * If any of the users are managers, we need their amount fields to be equal to their committed amount + the committed
     * amounts for the people who report to them.
     */
    protected function loadManagerAmounts()
    {
        foreach($this->dataArray as $rep => $val) {
            if(empty($val['forecast_id'])) {
                $this->dataArray[$rep]['amount'] = 0;
            } else if($val['user_id'] != $this->getArg('user_id') && $val['show_opps'] == false) {
                // this is for a a manager only row
                // we need to get their total amount including sales reps.
                // first get the reportees that have a forecast submitted for this time period
                $manager_reportees_forecast = $this->getUserReporteesWithForecasts($val['user_id'], $this->getArg('timeperiod_id'));
                // second, we need to get the data all the reporting users
                $manager_data = $this->getUserAmounts($val['user_id']);
                // third we only process the users that actually have a committed forecast;
                foreach($manager_data as $name => $m_data) {
                    if(in_array($name, $manager_reportees_forecast)) {
                        // add it to the managers amount
                        $this->dataArray[$rep]['amount'] += $m_data['amount'];
                    }
                }
            }
        }
    }

    /**
     * Get the report data with filters.
     *
     * @param null|string $user_id
     * @return array
     */
    protected function getUserAmounts($user_id = null)
    {
        if(empty($user_id)) {
            $user_id = $this->getArg('user_id');
        }

        $sql = "select u.user_name, sum(amount) as amount from opportunities o
INNER JOIN users u ON o.assigned_user_id = u.id and (u.reports_to_id = '". $user_id. "' OR u.id = '". $user_id. "')
where o.timeperiod_id = '" . $this->getArg('timeperiod_id') . "'
GROUP BY u.id;";

        $db = DBManagerFactory::getInstance();

        $results = $db->query($sql);

        $return = array();
        while($row = $db->fetchByAssoc($results)) {
            $return[$row['user_name']] = array('amount' => $row['amount']);
        }

        return $return;
    }
}