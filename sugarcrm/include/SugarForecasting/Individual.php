<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once('include/SugarForecasting/AbstractForecast.php');
class SugarForecasting_Individual extends SugarForecasting_AbstractForecast implements SugarForecasting_ForecastSaveInterface
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
            "o.base_rate, " .
            "o.assigned_user_id, " .
            "w.id worksheet_id, " .
            "w.user_id w_user_id, " .
            "w.best_case w_best_case, " .
            "w.likely_case w_likely_case, " .
            "w.worst_case w_worst_case, " .
            "w.forecast_type w_forecast_type, " .
            "w.related_id w_related_id, " .
            "w.version w_version, " .
            "w.commit_stage w_commit_stage, " .
            "w.op_probability w_probability, " .
            "w.currency_id w_currency_id, " .
            "w.base_rate w_base_rate " .
            "from opportunities o " .
            "left join timeperiods t ".
            "on t.start_date_timestamp < o.date_closed_timestamp ".
            "and t.end_date_timestamp >= o.date_closed_timestamp ".
            "left join worksheet w " .
            "on o.id = w.related_id ";
        if ($this->getArg('user_id') == $current_user->id) {
            $sql .= "and w.date_modified = (select max(date_modified) from worksheet w2 " .
                "where w2.user_id = o.assigned_user_id and related_id = o.id " .
                "and timeperiod_id = '" . $this->getArg('timeperiod_id') . "') ";
        } else {
            $sql .= "and w.version = 1 ";
        }

        $sql .= "where t.id =  '" . $this->getArg('timeperiod_id') . "' " .
            " and o.assigned_user_id = '" . $this->getArg('user_id') . "' " .
            "and o.deleted = 0";

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
            $data['currency_id'] = $row["currency_id"];
            $data['base_rate'] = $row["base_rate"];

            if (isset($row["worksheet_id"])) {
                $data['worksheet_id'] = $row["worksheet_id"];
                $data['best_case'] = $row["w_best_case"];
                $data['worst_case'] = $row["w_worst_case"];
                $data['amount'] = $row["w_likely_case"];
                $data['commit_stage'] = $row["w_commit_stage"];
                $data['probability'] = $row["w_probability"];
                $data['version'] = $row["w_version"];
            } else {
                $data['best_case'] = $row["best_case"];
                $data['worst_case'] = $row["worst_case"];
                $data['commit_stage'] = $row["commit_stage"];
                $data['probability'] = $row["probability"];
            }
            $this->dataArray[] = $data;
        }
    }

    /**
     * Save the Individual Worksheet
     *
     * @return mixed
     * @throws SugarApiExceptionNotAuthorized
     */
    public function save()
    {
        require_once('modules/Forecasts/ForecastWorksheet.php');
        require_once('include/SugarFields/SugarFieldHandler.php');
        $seed = new ForecastWorksheet();
        $seed->loadFromRow($this->getArgs());
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
		$seed->setWorksheetArgs($this->getArgs());
        $seed->save();
        return $seed->id;
    }
}