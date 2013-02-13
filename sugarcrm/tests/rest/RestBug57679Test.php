<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once('tests/rest/RestTestPortalBase.php');

class Bug57679Test extends RestTestPortalBase
{
    /**
     * The old value for num_portal_users if it is set but empty
     * @var string
     */
    protected $_oldNumUsers = null;
    
    /**
     * Flag for whether the num_portal_users setting was created
     * @var boolean
     */
    protected $_createdSetting = false;
    
    /**
     * Admin object
     * @var Admin
     */
    protected $_admin;
    
    public function setUp()
    {
        parent::setUp();
        
        // Build 10 KBDocuments for testing
        for ($i = 1; $i < 11; $i++) {
            $kbdoc = new KBDocument();
            $kbdoc->kbdocument_name = "KBDocument bug57679 #{$i} - " . create_guid_section(6);
            $kbdoc->body = 'UNIT TEST GENERATED DOCUMENT #' . $i;
            $startDate = new SugarDateTime();
            $startDate->modify('-7 weeks');
            $endDate = new SugarDateTime();
            $endDate->modify('+7 weeks');
            $kbdoc->active_date = $startDate->format('Y-m-d');
            $kbdoc->exp_date = $endDate->format('Y-m-d');
            $kbdoc->status_id = 'Published';
            $kbdoc->is_external_article = '1';
            $kbdoc->save();
            $this->kbdocs[] = $kbdoc;
        }
        
        // Make sure portal is setup
        $this->_admin = new Administration();
        $this->_admin->retrieveSettings();
        if (isset($this->_admin->settings['license_num_portal_users'])) {
            if (empty($this->_admin->settings['license_num_portal_users'])) {
                // 0 or ''
                $this->_oldNumUsers = $this->_admin->settings['license_num_portal_users'];
            }
        } else {
            $this->_createdSetting = true;
            $this->_admin->settings['license_num_portal_users'] = 50;
            $this->_admin->saveSetting('license', 'num_portal_users', '50');
        }
    }
    
    public function tearDown()
    {
        // If we created the num portal users setting, delete it
        if ($this->_createdSetting) {
            $sql = "DELETE FROM config WHERE category = 'license' AND name = 'num_portal_users'";
            $GLOBALS['db']->query($sql);
        } else {
            // If there was an old empty non-null value, set it back
            if ($this->_oldNumUsers !== null) {
                $this->_admin->settings['license_num_portal_users'] = $this->_oldNumUsers;
                $this->_admin->saveSetting('license', 'num_portal_users', $this->_oldNumUsers);
            }
        }
        
        parent::tearDown(); // This handles cleanup of created kbdocs
    }
    
    public function testDeletedKBDocsRecordsNotReturnedInList()
    {
        // First run, get the list of KBDocs... count should be 10
        $reply = $this->_restCall("KBDocuments?q=".rawurlencode("KBDocument bug57679")."&fields=kbdocument_name,active_date,exp_date,date_entered,kbdocument_revision_number&max_num=20&order_by=date_modified:desc");
        $records = $reply['reply']['records'];
        $this->assertNotEmpty($records, "First rest reply is empty and should not be");
        $count = count($records);
        $this->assertEquals(10, $count, "Result count should be 10 but is actually $count");
        
        // Now pick one and delete it
        $delete = $this->kbdocs[6];
        $delete->mark_deleted($delete->id);
        
        // Now get the list again... count should be 9
        $reply = $this->_restCall("KBDocuments?q=".rawurlencode("KBDocument bug57679")."&fields=kbdocument_name,active_date,exp_date,date_entered,kbdocument_revision_number&max_num=20&order_by=date_modified:desc");
        $records = $reply['reply']['records'];
        $this->assertNotEmpty($records, "Second rest reply is empty and should not be");
        $count = count($records);
        $this->assertEquals(9, $count, "Result count should be 9 but is actually $count");
    }
}