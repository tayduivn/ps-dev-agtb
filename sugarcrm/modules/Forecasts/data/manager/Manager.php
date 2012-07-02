<?php

require_once('modules/Forecasts/data/IChartAndWorksheet.php');

class Manager implements IChartAndWorksheet {

    private $def = array (
        'opportunities' => array('Opportunities', 'ForecastSeedManagerReport', '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"user_name","label":"User Name","table_key":"Opportunities:assigned_user_link","type":"user_name","force_label":"User Name"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum","force_label":"Sales Stage"}],"summary_columns":[{"name":"user_name","label":"User Name","table_key":"Opportunities:assigned_user_link"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"amount","label":"SUM: Opportunity Amount","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"test forecast manager","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:amount_usdollar:sum","numerical_chart_column_type":"","assigned_user_id":"seed_jim_id","report_type":"summary","layout_options":"2x2","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["group_by_row_1","display_summaries_row_group_by_row_1","group_by_row_1","display_summaries_row_group_by_row_1","group_by_row_1","display_summaries_row_group_by_row_1"],"module":"Users","label":"Assigned to User"}},"filters_def":[]}', 'Matrix', 'vBarF')

    );

    public $user_id;
    public $timeperiod_id;

    public function getFilters ()
    {
        $filters = array();

        $filters['assigned_user_link'] = array('id' => array('$or' => array('$is' => $this->user_id, '$reports' => $this->user_id)));
        $filters['timeperiod_id'] = array('$is' => $this->timeperiod_id);

        // also we can define other filters such as 'probability', 'sales_stage' etc.

        return $filters;
    }

    public function getGridData($report)
    {
        $report->run_summary_query();

        $data_grid = array();

        while(($row=$GLOBALS['db']->fetchByAssoc($report->summary_result))!=null)
        {
            $data_grid[$row['l1_user_name']]['amount'] += $row['opportunities_sum_amount'];
        }

        //get quota + best/likely (forecast) + best/likely (worksheet)

        $quota = $this->getQuota();
        $forecast = $this->getForecastBestLikely();
        $worksheet = $this->getWorksheetBestLikelyAdjusted();

        $data_grid = array_merge_recursive($data_grid, $quota, $forecast, $worksheet);

        return $data_grid;
    }

    function getQuota()
    {
        //getting quotas from quotas table
        $quota_query = "SELECT u.user_name user_name,
                              q.amount quota
                        FROM quotas q, users u
                        WHERE q.user_id = u.id
                        AND (q.user_id = '{$this->user_id}' OR q.user_id IN (SELECT id FROM users WHERE reports_to_id = '{$this->user_id}'))
                        AND q.timeperiod_id = '{$this->timeperiod_id}'
                        AND q.quota_type = 'Direct'";

        $result = $GLOBALS['db']->query($quota_query);

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['quota'] = $row['quota'];
        }

        return $data;
    }

    function getForecastBestLikely()
    {
        //getting best/likely values from forecast table
        $forecast_query = "SELECT u.user_name user_name,
                        f.best_case best,
                        f.likely_case likely
                        FROM forecasts f, users u
                        WHERE f.user_id = u.id
                        AND f.timeperiod_id = '{$this->timeperiod_id}'
                        AND ((f.user_id = '{$this->user_id}' AND f.forecast_type = 'Direct')
                            OR (f.user_id in (SELECT id from users WHERE reports_to_id = '{$this->user_id}') AND f.forecast_type = 'Rollup'))
                        AND f.date_modified = (SELECT MAX(date_modified) FROM forecasts WHERE user_id = u.id)";

        $result = $GLOBALS['db']->query($forecast_query);

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['best'] = $row['best'];
            $data[$row['user_name']]['likely'] = $row['likely'];
        }

        return $data;
    }

    function getWorksheetBestLikelyAdjusted()
    {
        //getting data from worksheet table for reportees
        $reportees_query = "SELECT u.user_name user_name,
                            w.best_case best_adjusted,
                            w.likely_case likely_adjusted
                            FROM worksheet w, users u
                            WHERE w.related_id = u.id
                            AND w.timeperiod_id = '{$this->timeperiod_id}'
                            AND w.user_id = '{$this->user_id}'
                            AND w.related_id in (SELECT id from users WHERE reports_to_id = '{$this->user_id}')
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
                            AND o.timeperiod_id = '{$this->timeperiod_id}'
                            AND o.assigned_user_id = '{$this->user_id}'
                            GROUP BY user_name";


        $result = $GLOBALS['db']->query($manager_query);

        while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null)
        {
            $data[$row['user_name']]['best_adjusted'] = $row['best_adjusted'];
            $data[$row['user_name']]['likely_adjusted'] = $row['likely_adjusted'];
        }

        return $data;
    }


    public function getChartDefinition($id='')
    {
        return $this->getWorksheetDefinition($id);
    }

    public function getWorksheetDefinition($id='')
    {
        return isset($this->def[$id]) ? $this->def[$id] : array();
    }
}
