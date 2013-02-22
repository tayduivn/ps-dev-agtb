<?php
//FILE SUGARCRM flav=pro ONLY
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/SugarFields/Fields/Teamset/SugarFieldTeamset.php');

/**
 * Bug #40003
 * Teams revert to self when Previewing a report
 * @ticket 40003
 */
class Bug40003Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function provider()
    {
        return array(
            array('Global', '1', 'Team_1', '123', '1'),
            array('Global', '1', 'Team_2', '111', '0')
        );
    }

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $_REQUEST['record'] = '';
        $_REQUEST['module'] = 'Reports';
        $this->fields = array('team_name' => array('name' => 'team_name'));
        $this->sft = new SugarFieldTeamset('Teamset');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    
    public function tearDown()
    {
        $_REQUEST = array();
        $_POST = array();
        SugarTestHelper::tearDown();
    }


    /**
     * @dataProvider provider
     * @group 40003
     */
    public function testGetTeamsFromPostWhilePreview($global_name, $global_id, $other_team_name, $other_team_name_id, $primary_collection)
    {
        $_POST['team_name_collection_0'] = $global_name;
        $_POST['id_team_name_collection_0'] = $global_id;
        $_POST['team_name_collection_1'] = $other_team_name;
        $_POST['id_team_name_collection_1'] = $other_team_name_id;
        $_POST['primary_team_name_collection'] = $primary_collection;
        $this->sft->initClassicView($this->fields);
        $this->assertEquals($this->sft->getPrimaryTeamIdFromRequest($this->sft->field_name, $_POST),
                            $this->sft->view->bean->team_set_id_values['primary']['id']);
    }
}
?>
