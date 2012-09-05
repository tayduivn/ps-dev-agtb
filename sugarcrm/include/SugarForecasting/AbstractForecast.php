<?php

abstract class SugarForecasting_AbstractForecast
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
     * @var SugarForecasting_Data_AbstractData
     */
    protected $dataClass;

    /**
     * Class Constructor
     * @param array $args       Service Arguments
     */
    public function __construct($args)
    {
        $this->setArgs($args);

        // load the data class into this class
        //$this->getForecastDataClass();
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

    /**
     * Common code to generate the report builder
     *
     * @param string|SugarBean $module      Which module are we basing this off of
     * @param string $report_base           The base report to start with in a json string
     * @param array $filters                What filters to apply
     * @return ReportBuilder
     */
    protected function generateReportBuilder($module, $report_base, $filters)
    {
        // make sure module is a string and not a sugar bean
        if ($module instanceof SugarBean) {
            $module = $module->module_dir;
        }

        // create the a report builder instance
        $rb = new ReportBuilder($module);
        // load the default report into the report builder
        $rb->setDefaultReport($report_base);

        // create the filter parser with the base module
        $filter = new SugarParsers_Filter(BeanFactory::getBean($module));
        $filter->parse($filters);
        // convert the filters into a reporting engine format
        $converter = new SugarParsers_Converter_Report($rb);
        $reportFilters = $filter->convert($converter);
        // add the filter to the report builder
        $rb->addFilter($reportFilters);

        // handle any group by if it is set
        $this->processGroupBy($rb);

        // return the report builder
        return $rb;
    }

    /**
     * Process any group by's that might be in the args
     *
     * Overwrite in the class if you want to use this method
     *
     * @param ReportBuilder $rb            ReportBuilder Instance
     * @return ReportBuilder
     */
    protected function processGroupBy(ReportBuilder $rb)
    {
        return $rb;
    }

    protected function getForecastDataClass()
    {
        if ($this->isManager) {
            $file = "include/SugarForecasting/Data/Manager.php";
            $class = "SugarForecasting_Data_Manager";
        } else {
            $file = "include/SugarForecasting/Data/Individual.php";
            $class = "SugarForecasting_Data_Individual";
        }

        $file = get_custom_file_if_exists($file);

        if (strpos($file, "custom") !== false) {
            $class = "Custom_" . $class;
        }

        require_once($file);

        $this->dataClass = new $class();
    }
}
