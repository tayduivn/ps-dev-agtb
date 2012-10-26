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

require_once('include/SugarForecasting/Export/AbstractExport.php');
require_once('include/SugarForecasting/Individual.php');
class SugarForecasting_Export_Individual extends SugarForecasting_Export_AbstractExport
{
    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct($args)
    {
        $this->isManager = false;
        parent::__construct($args);
    }


    public function process()
    {
        global $current_user;

        // base file and class name
        $file = 'include/SugarForecasting/Individual.php';
        $klass = 'SugarForecasting_Individual';

        // check for a custom file exists
        $include_file = get_custom_file_if_exists($file);

        // if a custom file exists then we need to rename the class name to be Custom_
        if ($include_file != $file) {
            $klass = "Custom_" . $klass;
        }

        // include the class in since we don't have a auto loader
        require_once($include_file);

        $obj = new $klass($this->args);
        $data = $obj->process();

        $fields_array = array(
            'id'=>'id',
            'product_id'=>'product_id',
            'date_closed'=>'date_closed',
            'sales_stage'=>'sales_stage',
            'assigned_user_id'=>'assigned_user_id',
            'amount'=>'amount',
            'worksheet_id'=>'worksheet_id',
            'name'=>'name',
            'currency_id'=>'currency_id',
            'base_rate'=>'base_rate',
            'best_case'=>'best_case',
            'worst_case'=>'worst_case',
            'likely_case'=>'likely_case',
            'commit_stage'=>'commit_stage',
            'probability'=>'probability',
        );

        $seed = BeanFactory::getBean('ForecastWorksheets');

        return $this->getContent($data, $seed, $fields_array);
    }

}