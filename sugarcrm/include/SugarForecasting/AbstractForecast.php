<?php

require_once('include/SugarForecasting/ForecastInterface.php');

abstract class SugarForecasting_AbstractForecast implements SugarForecasting_ForecastInterface
{
    /**
     * @var array Rest Arguments
     */
    protected $args;

    /**
     * Are we a manager
     *
     * @var bool
     */
    protected $isManager = false;

    /**
     * Where we store the data we want to use
     *
     * @var array
     */
    protected $dataArray = array();

    /**
     * Class Constructor
     * @param array $args       Service Arguments
     */
    public function __construct($args)
    {
        $this->setArgs($args);
    }

    /**
     * Set the arguments
     *
     * @param array $args
     * @return SugarForecasting_AbstractForecast
     */
    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Return the arguments array
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Get a specific Arg Value, If it doesn't exist return Empty
     *
     * @param $key
     * @return string
     */
    public function getArg($key)
    {
        return isset($this->args[$key]) ? $this->args[$key] : "";
    }

    /**
     * Set an Arg to track
     *
     * @param string $key
     * @param mixed $value
     * @return SugarForecasting_AbstractForecast
     */
    public function setArg($key, $value)
    {
        $this->args[$key] = $value;

        return $this;
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
     * Get the months for the current time period
     *
     * @param $timeperiod_id
     * @return array
     */
    protected function getTimePeriodMonths($timeperiod_id)
    {
        /* @var $timeperiod TimePeriod */
        $timeperiod = BeanFactory::getBean('TimePeriods', $timeperiod_id);

        $months = array();

        $start = strtotime($timeperiod->start_date);
        $end = strtotime($timeperiod->end_date);
        while ($start < $end) {
            $months[] = date('F Y', $start);
            $start = strtotime("+1 month", $start);
        }

        return $months;
    }

    /**
     * Get the direct reportees for a user.
     *
     * @param $user_id
     * @return array
     */
    protected function getUserReportees($user_id)
    {
        $db = DBManagerFactory::getInstance();
        $sql = $db->getRecursiveSelectSQL('users', 'id', 'reports_to_id',
            'id, user_name, first_name, last_name, reports_to_id, _level', false,
            "id = '{$user_id}' AND status = 'Active' AND deleted = 0", null, " AND status = 'Active' AND deleted = 0"
        );

        $result = $db->query($sql);

        $reportees = array();

        while ($row = $db->fetchByAssoc($result)) {
            if ($row['_level'] > 2) continue;

            if ($row['_level'] == 1) {
                $reportees = array_merge(array($row['id'] => $row['user_name']), $reportees);
            } else {
                $reportees[$row['id']] = $row['user_name'];
            }
        }

        return $reportees;
    }

    /**
     * Get the passes in users reportee's who have a forecast for the passed in time period
     *
     * @param string $user_id           A User Id
     * @param string $timeperiod_id     The Time period you want to check for
     * @return array
     */
    public function getUserReporteesWithForecasts($user_id, $timeperiod_id)
    {

        $db = DBManagerFactory::getInstance();
        $return = array();
        $query = "SELECT distinct users.user_name FROM users, forecasts
                WHERE forecasts.timeperiod_id = '" . $timeperiod_id . "' AND forecasts.deleted = 0
                AND users.id = forecasts.user_id AND (users.reports_to_id = '" . $user_id . "')";

        $result = $db->query($query, true, " Error fetching user's reporting hierarchy: ");
        while (($row = $db->fetchByAssoc($result)) != null) {
            $return[] = $row['user_name'];
        }

        return $return;
    }
}
