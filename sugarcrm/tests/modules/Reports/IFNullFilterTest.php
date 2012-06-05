<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once('modules/Forecasts/Worksheet.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/TimePeriods/TimePeriod.php');

class IFNULLFilterTest extends Sugar_PHPUnit_Framework_TestCase
{

    var $opportunity;
    var $worksheet;
    var $timeperiod;

    public function setUp()
    {
        global $current_user, $beanList, $beanFiles;
        require('include/modules.php');

        $this->timeperiod = TimePeriod::getTimePeriod();

        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $this->opportunity->assiged_user_id = $current_user->id;
        $this->opportunity->best_case = 100.00;
        $this->opportunity->likely_case = 90.00;
        $this->opoprtunity->worst_case = 80.00;
        $this->opportunity->timeperiod_id = $this->timeperiod->id;
        $this->opportunity->save();

        $this->worksheet = SugarTestWorksheetUtilities::createWorksheet();
        $this->worksheet->best_case = 90.00;
        $this->worksheet->likely_case = 80.00;
        $this->worksheet->worst_case = 70.00;
        $this->worksheet->related_id = $this->opportunity->id;
        $this->worksheet->related_forecast_type = 'Direct';
        $this->worksheet->timeperiod_id = $this->timeperiod->id;
        $this->worksheet->save();

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
    }

    /**
     * testIfNullFilters
     * 
     */
    public function testIfNullFilters()
    {
        $ifNull = $GLOBALS['db']->convert('worksheet.best_case, opportunities.best_case', 'ifnull');
        $sql = "SELECT {$ifNull} FROM worksheet LEFT JOIN opportunities ON worksheet.related_id = opportunities.id";
        $sql .= " WHERE opportunities.id = '{$this->opportunity->id}' AND opportunities.timeperiod_id = '{$this->timeperiod->id}'";
        $result = $GLOBALS['db']->getOne($sql);
        $this->assertEquals(90, (int)$result);

        $this->worksheet->best_case = ''; //This is equivalent to null???
        $this->worksheet->save();

        $ifNull = $GLOBALS['db']->convert('worksheet.best_case, opportunities.best_case', 'ifnull');
        $sql = "SELECT {$ifNull} FROM worksheet LEFT JOIN opportunities ON worksheet.related_id = opportunities.id";
        $sql .= " WHERE opportunities.id = '{$this->opportunity->id}' AND opportunities.timeperiod_id = '{$this->timeperiod->id}'";
        $result = $GLOBALS['db']->getOne($sql);
        $this->assertEquals(100, (int)$result);
    }

}
