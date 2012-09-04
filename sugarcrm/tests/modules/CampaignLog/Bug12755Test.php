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

require_once('modules/Campaigns/ProcessBouncedEmails.php');

class Bug12755Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $emailAddress = 'unittest@example.com';
    protected $_user;

    public function setUp()
    {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->emailAddress->addAddress($this->emailAddress, false, false, 0);
        $this->_user->emailAddress->save($this->_user->id, $this->_user->module_dir);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $query = "DELETE from email_addresses where email_address = '{$this->emailAddress}'";
        $GLOBALS['db']->query($query);
        $query = "DELETE from email_addr_bean_rel where bean_id = '{$this->_user->id}'";
        $GLOBALS['db']->query($query);
    }

    public function testMarkEmailAddressInvalid()
    {
        markEmailAddressInvalid($this->emailAddress);

        $sea = new SugarEmailAddress();
        $rs = $sea->retrieve_by_string_fields( array('email_address_caps' => trim(strtoupper($this->emailAddress))) );
        $this->assertTrue( (bool) $rs->invalid_email);
    }

}