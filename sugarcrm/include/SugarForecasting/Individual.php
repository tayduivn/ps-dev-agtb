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
    public function process($execute=true)
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        $query = "select o.id opp_id, " .
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
        	   	"from products p " .
        	   	"inner join timeperiods t " .
        	   		"on t.id = '" . $this->getArg('timeperiod_id') . "' " .
        	   		"and p.date_closed_timestamp >= t.start_date_timestamp " .
        	   		"and p.date_closed_timestamp <= t.end_date_timestamp " .
        	   		"and p.assigned_user_id = '" . $this->getArg('user_id') . "' " .
        	   	"inner join opportunities o " .
        	   		"on p.opportunity_id = o.id " .
        	   	"left join worksheet w " .
        	   	"on p.id = w.related_id "; 

        if ($this->getArg('user_id') == $current_user->id) {
            $query .= "and w.date_modified = (select max(date_modified) from worksheet w2 " .
                "where w2.user_id = p.assigned_user_id and related_id = p.id " .
                "and timeperiod_id = '" . $this->getArg('timeperiod_id') . "') ";
        } else {
            $query .= "and w.version = 1 ";
        }
        
		$query .= "where p.deleted = 0 " .
				"and o.deleted = 0 ";

        //If execute is set to false just return the query
        if(!$execute)
        {
           return $query;
        }

        $result = $db->query($query);

        while (($row = $db->fetchByAssoc($result)) != null) {
            $data = array();
            $data['id'] = $row["opp_id"];
            $data['product_id'] = $row["product_id"];
            $data['date_closed'] = $row["date_closed"];
            $data['sales_stage'] = $row["sales_stage"];
            $data['assigned_user_id'] = $row["assigned_user_id"];
            $data['amount'] = $row["likely_case"];
            $data['worksheet_id'] = "";
            $data['name'] = $row["name"];
            $data['currency_id'] = $row["currency_id"];
            $data['base_rate'] = $row["base_rate"];
            $data['version'] = 1;

            if (isset($row["worksheet_id"])) {
            	//use the worksheet data if it exists
                $data['worksheet_id'] = $row["worksheet_id"];
                $data['best_case'] = $row["w_best_case"];
                $data['likely_case'] = $row["w_likely_case"];
                $data['worst_case'] = $row["w_worst_case"];
                $data['amount'] = $row["w_likely_case"];
                $data['commit_stage'] = $row["w_commit_stage"];
                $data['probability'] = $row["w_probability"];
                $data['version'] = $row["w_version"];
            } else {
                //Set default values to that of the product's
                $data['best_case'] = $row["best_case"];
                $data['likely_case'] = $row["likely_case"];
                $data['worst_case'] = $row["worst_case"];
                $data['commit_stage'] = $row["commit_stage"];
                $data['probability'] = $row["probability"];
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
     * @return mixed
     * @throws SugarApiExceptionNotAuthorized
     */
    public function save()
    {
        require_once('modules/Forecasts/ForecastWorksheet.php');
        require_once('include/SugarFields/SugarFieldHandler.php');
        $seed = new ForecastWorksheet();
        $seed->loadFromRow($this->args);
        $sfh = new SugarFieldHandler();

        foreach ($seed->field_defs as $properties)
        {
            $fieldName = $properties['name'];

            if(!isset($this->args[$fieldName]))
            {
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

            if($field != null)
            {
               $field->save($seed, $this->args, $fieldName, $properties);
            }
        }
		$seed->setWorksheetArgs($this->args);
        $seed->save();
        return $seed->id;
    }
}