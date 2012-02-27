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

require_once('modules/Contacts/ContactFormBase.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Leads/LeadFormBase.php');
require_once('modules/Leads/Lead.php');

/**
 * Bug #46427
 * Records from other Teams shown on Potential Duplicate Contacts screen during Lead Conversion
 *
 * @ticket 46427
 */
class Bug46427Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->createPOST();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);
        $this->clearPOST();
    }

    private function createPOST()
    {
        $_POST['first_name'] = 'FIRST_NAME';
        $_POST['last_name'] = 'LAST_NAME';
    }

    private function clearPOST()
    {
        unset($_POST['first_name'], $_POST['last_name']);
    }

    /**
     * @group 46427
     */
    public function testGetDuplicateQueryContact()
    {
        $focus = $this->getMock('Contact');
        $focus->disable_row_level_security = false;
        $focus->expects($this->once())->method('add_team_security_where_clause');

        $form = new ContactFormBase();
        $form->getDuplicateQuery($focus);
    }

    /**
     * @group 46427
     */
    public function testGetDuplicateQueryContact2()
    {
        $focus = $this->getMock('Contact');
        $focus->disable_row_level_security = true;
        $focus->expects($this->never())->method('add_team_security_where_clause');

        $form = new ContactFormBase();
        $form->getDuplicateQuery($focus);
    }

    /**
     * @group 46427
     */
    public function testGetDuplicateQueryLead()
    {
        $focus = $this->getMock('Lead');
        $focus->disable_row_level_security = false;
        $focus->expects($this->once())->method('add_team_security_where_clause');

        $form = new LeadFormBase();
        $form->getDuplicateQuery($focus);
    }

    /**
     * @group 46427
     */
    public function testGetDuplicateQueryLead2()
    {
        $focus = $this->getMock('Lead');
        $focus->disable_row_level_security = true;
        $focus->expects($this->never())->method('add_team_security_where_clause');

        $form = new LeadFormBase();
        $form->getDuplicateQuery($focus);
    }
}