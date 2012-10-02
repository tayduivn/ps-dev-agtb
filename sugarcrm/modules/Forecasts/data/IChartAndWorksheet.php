<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * IChartAndWorksheet.php
 *
 * This is an interface used by the Forecasts modules to separate returning chart and worksheet data for an individual sales
 * rep and manager view.  See modules/Forecasts/data/Individual/Individual.php and modules/Forecasts/data/Manager/Manager.php
 *
 */
interface IChartAndWorksheet
{
    /**
     * Returns the Report's module JSON encoded format chart definition as a String.  Implementations should have a definition
     * created in the format of the Reports module and return this report definition string.
     *
     * @abstract
     *
     * @param string $id            Optional string id in the event there may be multiple worksheet data definitions
     * @return mixed
     */
    public function getWorksheetDefinition($id = '');


    /**
     * This method returns the report results that represent the data for the individual or manager worksheets.  Implementations
     * should pass in a Report instance that is expected to return row data.
     *
     * @param Report $report Report instance to retrieve data from
     * @return Array report data results
     */
    public function getGridData(Report $report);


}