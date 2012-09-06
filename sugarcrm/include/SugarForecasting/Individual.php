<?php

require_once('include/SugarForecasting/AbstractForecast.php');
class SugarForecasting_Individual extends SugarForecasting_AbstractForecast
{
    /**
     * Where we store the data we want to use
     *
     * @var array
     */
    protected $dataArray = array();

    /**
     * Run all the tasks we need to process get the data back
     *
     * @return array|string
     */
    public function process()
    {
        $this->loadWorksheet();

        return array_values($this->dataArray);
    }

    protected function loadWorksheet()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

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
        if ($this->getArg('user_id') == $current_user->id) {
            $sql .= "and w.date_modified = (select max(date_modified) from worksheet w2 " .
                "where w2.user_id = o.assigned_user_id and related_id = o.id " .
                "and timeperiod_id = '" . $this->getArg('timeperiod_id') . "') ";
        } else {
            $sql .= "and w.version = 1 ";
        }

        $sql .= "where o.timeperiod_id = '" . $this->getArg('timeperiod_id') . "' " .
            "and o.assigned_user_id = '" . $this->getArg('user_id') . "'";

        $result = $db->query($sql);

        while (($row = $db->fetchByAssoc($result)) != null) {
            $data = array();
            $data['id'] = $row["id"];
            $data['date_closed'] = $row["date_closed"];
            $data['sales_stage'] = $row["sales_stage"];
            $data['assigned_user_id'] = $row["assigned_user_id"];
            $data['amount'] = $row["amount"];
            $data['worksheet_id'] = "";
            $data['name'] = $row["name"];

            if (isset($row["worksheet_id"])) {
                $data['worksheet_id'] = $row["worksheet_id"];
                $data['forecast'] = $row["w_forecast"];
                $data['best_case'] = $row["w_best_case"];
                $data['worst_case'] = $row["w_worst_case"];
                $data['likely_case'] = $row["w_likely_case"];
                $data['commit_stage'] = $row["w_commit_stage"];
                $data['probability'] = $row["w_probability"];
                $data['version'] = $row["w_version"];
            } else {
                $data['forecast'] = $row["forecast"];
                $data['best_case'] = $row["best_case"];
                $data['worst_case'] = $row["worst_case"];
                $data['commit_stage'] = $row["commit_stage"];
                $data['probability'] = $row["probability"];
            }
            $this->dataArray[] = $data;
        }
    }
}