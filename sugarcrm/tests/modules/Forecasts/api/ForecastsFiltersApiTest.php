<?php
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

require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 * @group forecasts
 */
class ForecastsFiltersApiTest extends RestTestBase
{

    private $currentUser;
    private $employee1;
    private $employee2;
    private $employee3;
    private $employee4;

    public function setUp()
    {
        parent::setUp();

        $this->currentUser = SugarTestUserUtilities::createAnonymousUser();
        $this->currentUser->user_name = 'employee0';
        $this->currentUser->save();

        $this->employee1 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee1->reports_to_id = $this->currentUser->id;
        $this->employee1->user_name = 'employee1';
        $this->employee1->save();

        $this->employee2 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee2->reports_to_id = $this->currentUser->id;
        $this->employee2->user_name = 'employee2';
        $this->employee2->save();

        $this->employee3 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee3->reports_to_id = $this->employee2->id;
        $this->employee3->user_name = 'employee3';
        $this->employee3->save();

        $this->employee4 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee4->reports_to_id = $this->employee3->id;
        $this->employee4->user_name = 'employee4';
        $this->employee4->save();

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /***
     * @group forecastapi
     */
    public function testReportees() {

        $restReply = $this->_restCall("Forecasts/reportees/" . $this->currentUser->id);
        $this->assertEquals($restReply['reply']['metadata']['id'], $this->currentUser->id, "currentUser's id was not found in the Expected place in the rest reply" );

        // get the user ids from first level
        $firstLevel = array( $restReply['reply']['children'][0]['metadata']['id'], $restReply['reply']['children'][1]['metadata']['id']);

        // assertContains in case the order is ever jumbled
        $this->assertContains($this->employee1->id, $firstLevel, "employee1's id was not found in the Expected place in the rest reply" );
        $this->assertContains($this->employee2->id, $firstLevel, "employee2's id was not found in the Expected place in the rest reply" );
    }

    public function testTimeperiods()
    {
        $restReply = $this->_restCall("Forecasts/filters/");

        $db = DBManagerFactory::getInstance();

        $result = $db->query('SELECT id, name FROM timeperiods WHERE is_fiscal_year = 1 AND deleted=0');
        while(($row = $db->fetchByAssoc($result)))
        {
            $fiscal_timeperiods[$row['id']]=$row['name'];
        }

        foreach($fiscal_timeperiods as $ftp)
        {
            $this->assertNotContains($ftp, $restReply['reply']['timeperiod_id']['options'], "filter contains ". $ftp['name'] . " fiscal timeperiod");
        }
    }

}
