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
     * @param $execute boolean indicating whether or not to execute the query and return the results; defaults to true
     * @return array|string
     */
    public function process()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        $sql = "select o.id opp_id, " .
                       "p.probability, " .
                       "p.commit_stage, " .
                       "o.sales_stage," .
                       "p.date_closed, " .
                       "p.currency_id, " .
                       "o.name, " .
                       "p.best_case, " .
                       "p.worst_case, " .
                       "p.likely_case, " .
                       "p.base_rate, " .
                       "p.assigned_user_id, " .
                       "p.id product_id, " .
                       "p.date_modified, " .
                       "w.id worksheet_id, " .
                       "w.assigned_user_id w_user_id, " .
                       "w.best_case w_best_case, " .
                       "w.likely_case w_likely_case, " .
                       "w.worst_case w_worst_case, " .
                       "w.parent_type w_forecast_type, " .
                       "w.parent_id w_related_id, " .
                       "w.draft w_version, " .
                       "w.commit_stage w_commit_stage, " .
                       "w.probability w_probability, " .
                       "w.currency_id w_currency_id, " .
                       "w.base_rate w_base_rate, " .
                       "w.date_modified w_date_modified " .
                   "from products p " .
                   "inner join timeperiods t " .
                       "on t.id = '" . $this->getArg('timeperiod_id') . "' " .
                       "and p.date_closed_timestamp >= t.start_date_timestamp " .
                       "and p.date_closed_timestamp <= t.end_date_timestamp " .
                       "and p.assigned_user_id = '" . $this->getArg('user_id') . "' " .
                   "inner join opportunities o " .
                       "on p.opportunity_id = o.id " .
                   "left join forecast_worksheets w " .
                   "on p.id = w.parent_id ";

        if ($this->getArg('user_id') != $current_user->id) {
               $sql .= "and w.draft = 1 ";
        }

        $sql .= "where p.deleted = 0 " .
                "and o.deleted = 0 ";
        $result = $db->query($sql);

        // use to_html when call DBManager::fetchByAssoc if encode_to_html isn't defined or not equal false
        // @see Bug #58397 : Comma in opportunity name is exported as #039;
        $encode_to_html = !isset($this->args['encode_to_html']) || $this->args['encode_to_html'] != false;

        while (($row = $db->fetchByAssoc($result, $encode_to_html)) != null) {
            
            /* if we are a manager looking at a reportee worksheet and they haven't committed anything yet 
             * (no worksheet row), we don't want to add this row to the output.
             */
            if(!isset($row["worksheet_id"]) && $this->getArg("user_id") != $current_user->id)
            {
                continue;
            }
            
            $data = array();
            $data["id"] = $row["opp_id"];
            $data["product_id"] = $row["product_id"];
            $data["date_closed"] = $row["date_closed"];
            $data["sales_stage"] = $row["sales_stage"];
            $data["assigned_user_id"] = $row["assigned_user_id"];
            $data["amount"] = $row["likely_case"];
            $data["worksheet_id"] = "";
            $data["name"] = $row["name"];
            $data["currency_id"] = $row["currency_id"];
            $data["base_rate"] = $row["base_rate"];
            $data["version"] = 1;
            $data["worksheet_id"] = $row["worksheet_id"];
            $data["date_modified"] = $this->convertDateTimeToISO($db->fromConvert($row["date_modified"], "datetime"));
            
            if(isset($row["worksheet_id"])){
            	$data["w_date_modified"] = $this->convertDateTimeToISO($db->fromConvert($row["w_date_modified"], "datetime"));
            }
            if (isset($row["worksheet_id"]) && $this->getArg("user_id") != $current_user->id) {
                //use the worksheet data if it exists
                $data["best_case"] = $row["w_best_case"];
                $data["likely_case"] = $row["w_likely_case"];
                $data["worst_case"] = $row["w_worst_case"];
                $data["amount"] = $row["w_likely_case"];
                $data["commit_stage"] = $row["w_commit_stage"];
                $data["probability"] = $row["w_probability"];
                $data["version"] = $row["w_version"];
            } else {
                //Set default values to that of the product"s
                $data["best_case"] = $row["best_case"];
                $data["likely_case"] = $row["likely_case"];
                $data["worst_case"] = $row["worst_case"];
                $data["commit_stage"] = $row["commit_stage"];
                $data["probability"] = $row["probability"];
            }
            $this->dataArray[] = $data;
        }

        return array_values($this->dataArray);
    }


    /**
     * getQuery
     *
     * This is a helper function to allow for the query function to be used in ForecastWorksheet->create_export_query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Save the Individual Worksheet
     *
     * @return ForecastWorksheet
     * @throws SugarApiException
     */
    public function save()
    {
        require_once('include/SugarFields/SugarFieldHandler.php');
        /* @var $seed ForecastWorksheet */
        $seed = BeanFactory::getBean("ForecastWorksheets");
        $seed->loadFromRow($this->args);
        $sfh = new SugarFieldHandler();

        foreach ($seed->field_defs as $properties) {
            $fieldName = $properties['name'];

            if(!isset($this->args[$fieldName])) {
               continue;
            }

            //BEGIN SUGARCRM flav=pro ONLY
            if (!$seed->ACLFieldAccess($fieldName,'save') ) {
                // No write access to this field, but they tried to edit it
                global $app_strings;
                throw new SugarApiException(string_format($app_strings['SUGAR_API_EXCEPTION_NOT_AUTHORIZED'], array($fieldName, $this->args['module'])));
            }
            //END SUGARCRM flav=pro ONLY

            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);

            if(!is_null($field)) {
               $field->save($seed, $this->args, $fieldName, $properties);
            }
        }

        //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        if (!isset($settings['has_commits']) || !$settings['has_commits']) {
            $admin->saveSetting('Forecasts', 'has_commits', true, 'base');
            MetaDataManager::clearAPICache();
        }

        $seed->setWorksheetArgs($this->args);
        // we need to set the parent_type and parent_id so it finds it when we try and retrieve the old records
        $seed->parent_type = $this->getArg('parent_type');
        $seed->parent_id = $this->getArg('parent_id');
        $seed->saveWorksheet();

        // we have the id, just retrieve the record again
        $seed = BeanFactory::getBean("ForecastWorksheets", $this->getArg('record'));

        return $seed;
    }
}