<?php

class ChartAndWorksheetManager {

private $dataMapping;
private $dataInstances;

private function ChartAndWorksheetManager()
{
    //first check if a custom file exists
    //there should be an Array named $chartAndWorksheetMapping if you decide on this course
    if(file_exists('custom/modules/Forecasts/data/config.php'))
    {
        require($configFile);
        $this->dataMapping = $chartAndWorksheetMapping;
    } else {
        //Default settings
        $this->dataMapping = array(
            'manager' => 'modules/Forecasts/data/manager/Manager.php',
            'individual' => 'modules/Forecasts/data/individual/Individual.php',
        );
    }
}

public static final function getInstance()
{
    static $instance;
    if(is_null($instance))
    {
       $instance = new ChartAndWorksheetManager();
    }
    return $instance;
}


/**
 * @param $type
 * @param string $id
 * @return mixed
 */
public function getChartDefintion($type, $id='')
{
    try {
      $instance = $this->getDataInstance($type);
      return $instance->getChartDefinition($id);
    } catch (Exception $ex) {
      return null;
    }
}


/**
 * @param $type
 * @param string $id
 * @return mixed
 */
public function getWorksheetDefintion($type, $id='')
{
    try {
      $instance = $this->getDataInstance($type);
      return $instance->getWorksheetDefinition($id);
    } catch (Exception $ex) {
      return null;
    }
}

/**
 *
 * @param $type String value of the data type to retrieve (ex: 'manager', 'individual')
 * @return IChartAndWorksheet Interface instance
 *
 * @throws Exception
 */
protected function getDataInstance($type)
{
    if(isset($this->instances[$type]))
    {
       return $this->dataInstances[$type];
    }

    if(!isset($this->dataMapping[$type]))
    {
       throw new Exception("Undefined type: [{$type}] in data mapping.");
    }

    $klass = ucfirst($type);

    require_once($this->dataMapping[$type]);
    return new $klass();
}

    function getQuota($user_id, $timeperiod_id)
    {
        //getting quotas from quotas table
        $quota_query = "SELECT u.user_name user_name,
                              q.amount quota
                        FROM quotas q, users u
                        WHERE q.user_id = u.id
                        AND (q.user_id = '{$user_id}' OR q.user_id IN (SELECT id FROM users WHERE reports_to_id = '{$user_id}'))
                        AND q.timeperiod_id = '{$timeperiod_id}'
                        AND q.quota_type = 'Direct'";

        $result = $GLOBALS['db']->query($quota_query);

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['quota'] = $row['quota'];
        }

        return $data;
    }

    function getForecastBestLikely($user_id, $timeperiod_id)
    {
        //getting best/likely values from forecast table
        $forecast_query = "SELECT u.user_name user_name,
                        f.best_case best,
                        f.likely_case likely
                        FROM forecasts f, users u
                        WHERE f.user_id = u.id
                        AND f.timeperiod_id = '{$timeperiod_id}'
                        AND ((f.user_id = '{$user_id}' AND f.forecast_type = 'Direct')
                            OR (f.user_id in (SELECT id from users WHERE reports_to_id = '{$user_id}') AND f.forecast_type = 'Rollup'))
                        AND f.date_modified = (SELECT MAX(date_modified) FROM forecasts WHERE user_id = u.id)";

        $result = $GLOBALS['db']->query($forecast_query);

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['best'] = $row['best'];
            $data[$row['user_name']]['likely'] = $row['likely'];
        }

        return $data;
    }

    function getWorksheetBestLikelyAdjusted($user_id, $timeperiod_id)
    {
        //getting data from worksheet table for reportees
        $reportees_query = "SELECT u.user_name user_name,
                            w.best_case best_adjusted,
                            w.likely_case likely_adjusted
                            FROM worksheet w, users u
                            WHERE w.related_id = u.id
                            AND w.timeperiod_id = '{$timeperiod_id}'
                            AND w.user_id = '{$user_id}'
                            AND w.related_id in (SELECT id from users WHERE reports_to_id = '{$user_id}')
                            AND w.forecast_type = 'Rollup'";

        $result = $GLOBALS['db']->query($reportees_query);

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['best_adjusted'] = $row['best_adjusted'];
            $data[$row['user_name']]['likely_adjusted'] = $row['likely_adjusted'];
        }

        //getting data from opportunities table for manager
        $manager_query = "SELECT u.user_name user_name,
                            SUM(o.best_case) best_adjusted,
                            SUM(o.likely_case) likely_adjusted
                            FROM users u, opportunities o
                            WHERE u.id = o.assigned_user_id
                            AND o.timeperiod_id = '{$timeperiod_id}'
                            AND o.assigned_user_id = '{$user_id}'
                            GROUP BY user_name";


        $result = $GLOBALS['db']->query($manager_query);

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['best_adjusted'] = $row['best_adjusted'];
            $data[$row['user_name']]['likely_adjusted'] = $row['likely_adjusted'];
        }

        return $data;
    }

}
