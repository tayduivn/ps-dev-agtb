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

/**
 * Bug 57839 - REST non-GET API must set no-cache headers in response
 */
class RestBug57839Test extends RestTestBase
{
    protected $_accountId;
    
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->_accountId}'");
        $GLOBALS['db']->commit();
        
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCorrectResponseHeadersForRequestTypes()
    {
        // Create an Account - POST
        $reply = $this->_restCall("Accounts/", json_encode(array('name'=>'UNIT TEST - AFTER')), 'POST');
        $this->assertTrue(isset($reply['reply']['id']), "An account was not created (or if it was, the ID was not returned)");
        $this->_accountId = $reply['reply']['id'];
        
        // Header assertions
        $this->assertNotEmpty($reply['headers']['Cache-Control'], "Cache-Control header missing after POST request");
        $this->assertEquals('no-cache, must-revalidate', $reply['headers']['Cache-Control'], "Incorrect Cache Control value for POST request");
        $this->assertNotEmpty($reply['headers']['Pragma'], "Pragma header missing after POST request");
        $this->assertEquals('no-cache', $reply['headers']['Pragma'], "Incorrect Pragma value for POST request");
        
        // Get the Account - GET with ETag
        $reply = $this->_restCall("Accounts/{$this->_accountId}");
        $this->assertTrue(isset($reply['reply']['id']), "Account ID was not returned");
        
        // Sugar REST GET reply includes empty Cache-Control and Pragma headers
        $this->assertFalse(isset($reply['headers']['Cache-Control']), "Cache-Control header had a value in the GET reply");
        $this->assertFalse(isset($reply['headers']['Pragma']), "Pragma header had a value in the GET reply");
        $this->assertNotEmpty($reply['headers']['ETag'], "ETag header missing from GET request");
        
        // Modify the Account - PUT
        $reply = $this->_restCall("Accounts/{$this->_accountId}", json_encode(array('name'=>'UNIT TEST - AFTER')), 'PUT');
        $this->assertTrue(isset($reply['reply']['id']), "Account ID was not returned in the PUT request");
        $this->assertEquals($this->_accountId, $reply['reply']['id'], "Account ID from reply is different from the create after PUT");
        
        // Header assertions
        $this->assertNotEmpty($reply['headers']['Cache-Control'], "Cache-Control header missing after PUT request");
        $this->assertEquals('no-cache, must-revalidate', $reply['headers']['Cache-Control'], "Incorrect Cache Control value for PUT request");
        $this->assertNotEmpty($reply['headers']['Pragma'], "Pragma header missing after PUT request");
        $this->assertEquals('no-cache', $reply['headers']['Pragma'], "Incorrect Pragma value for PUT request");
        
        // Delete the Account - DELETE
        $reply = $this->_restCall("Accounts/{$this->_accountId}", '', 'DELETE');
        $this->assertTrue(isset($reply['reply']['id']), "Account ID was not returned in the DELETE request");
        $this->assertEquals($this->_accountId, $reply['reply']['id'], "Account ID from reply is different from the create after DELETE");
        
        // Header assertions
        $this->assertNotEmpty($reply['headers']['Cache-Control'], "Cache-Control header missing after DELETE request");
        $this->assertEquals('no-cache, must-revalidate', $reply['headers']['Cache-Control'], "Incorrect Cache Control value for DELETE request");
        $this->assertNotEmpty($reply['headers']['Pragma'], "Pragma header missing after DELETE request");
        $this->assertEquals('no-cache', $reply['headers']['Pragma'], "Incorrect Pragma value for DELETE request");
    }
}
