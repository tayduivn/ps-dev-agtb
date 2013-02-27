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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/Import/ImportDuplicateCheck.php');

/**
 * Bug #51264
 * Importing updates to rows prevented by duplicates check
 *
 * @ticket 51264
 */
class Bug51264Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $contact;

    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->contact = SugarTestContactUtilities::createContact();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        unset($this->contact);
        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * @group 51264
     */
    public function testIsADuplicateRecordWithID()
    {
        $idc = new ImportDuplicateCheck($this->contact);
        $result = $idc->isADuplicateRecord(array('special_idx_email1::email1'));
        $this->assertFalse($result);
    }

    /**
     * @group 51264
     */
    public function testIsADuplicateRecordWithInvalidID()
    {
        $contact = new Contact();
        $contact->id = '0000000000000000';
        $contact->email1 = $this->contact->email1;
        $idc = new ImportDuplicateCheck($contact);
        $result = $idc->isADuplicateRecord(array('special_idx_email1::email1'));
        $this->assertTrue($result);
    }

    /**
     * @group 51264
     */
    public function testIsADuplicateRecordWithInvalidID2()
    {
        $contact = new Contact();
        $contact->id = '0000000000000000';
        $contact->email1 = 'Bug51264Test@Bug51264Test.com';
        $idc = new ImportDuplicateCheck($contact);
        $result = $idc->isADuplicateRecord(array('special_idx_email1::email1'));
        $this->assertFalse($result);
    }

    /**
     * @group 51264
     */
    public function testIsADuplicateRecord()
    {
        $contact = new Contact();
        $contact->email1 = $this->contact->email1;
        $idc = new ImportDuplicateCheck($contact);
        $result = $idc->isADuplicateRecord(array('special_idx_email1::email1'));
        $this->assertTrue($result);
    }
}