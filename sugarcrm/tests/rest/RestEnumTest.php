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

class RestEnumTest extends RestTestBase {
    public function tearDown() {
        if(isset($this->contract_types)) {
            foreach($this->contract_types AS $id => $name) {
                $GLOBALS['db']->query("DELETE FROM contract_types WHERE id='{$id}'");                
            }
        }
        
        parent::tearDown();
    }
    /**
     * @group rest
     */
    public function testFunctionBasedDropDown() {
            $contract_types = array(
                create_guid() => 'Unit Test 1',
                create_guid() => 'Unit Test 2',
            );
            $this->contract_types = $contract_types;
        foreach($contract_types AS $id => $name) {
            $ct = BeanFactory::newBean('ContractTypes');
            $ct->new_with_id = true;
            $ct->id = $id;
            $ct->name = $name;
            $ct->save();
        }
        $restReply = $this->_restCall('/Contracts/enum/type');
        // add the blank one
        $contract_types['']='';

        $this->assertEquals($contract_types, $restReply['reply']);
    }
    /**
     * @group rest
     */
    public function testETagHeaders() {
        $restReply = $this->_restCall('Products/enum/commit_stage');
        $this->assertNotEmpty($restReply['headers']['ETag']);
        $this->assertEquals($restReply['info']['http_code'], 200);
        $restReply = $this->_restCall('Products/enum/commit_stage', '', '', array(), array('If-None-Match: ' . $restReply['headers']['ETag']));
        $this->assertNotEmpty($restReply['headers']['ETag']);
        $this->assertEquals($restReply['info']['http_code'], 304);

    }
    /**
     * @group rest
     */
    public function testHtmlDropDown() {
        $restReply = $this->_restCall('Products/enum/type_id');
        $this->assertEquals('fatal_error',$restReply['reply']['error'], "Did not return a fatal error");
        $this->assertEquals('html dropdowns are not supported', $restReply['reply']['error_message'], "Did not return the correct error message");
    }

    /**
     * @group rest
     */
    public function testStandardDropDown() {
        $restReply = $this->_restCall('Products/enum/commit_stage');
        $this->assertTrue(!empty($restReply['reply']), "Commit Stage came back empty");
    }

    /**
     * @group rest
     */
    public function testNonExistantDropDown() {
        $restReply = $this->_restCall('Accounts/enum/UnitTest'.create_guid());
        $this->assertEquals('not_found', $restReply['reply']['error'], "Incorrect Error Returned");
        $this->assertEquals('field not found', $restReply['reply']['error_message'], "Incorrect message returned");
    }
}

