<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/Opportunities/Opportunity.php');

/**
 * SetCommitStageTest.php
 *
 * This is a test to check that the probability value for an opportunity correctly adjusts the commit_stage value
 * during a save operation.
 *
 */
class SetCommitStageTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $opp;

    public function setUp()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
    }

    public static function setupBeforeClass()
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }

    public static function tearDownAfterClass()
    {
        unset($GLOBALS['app_list_strings']);
    }

    public function probabilityProvider()
    {
        return array(
            array(0, "50"),
            array(25, "50"),
            array(65, "70"),
            array(85, "100"),
            array(100, "100")
        );
    }

    /**
     * Tests the probability against the expected commit_stage value with the supplied probabilityProvider function
     *
     * @dataProvider probabilityProvider
     */
    public function testSetCommitStage($probability, $commit_stage)
    {
        //Test setting field 'commit_stage'
        $opp = new Opportunity();
        $opp->probability = $probability;
        $opp->save();
        $this->assertEquals($opp->commit_stage, $commit_stage, "commit stage should be $commit_stage");
    }
}