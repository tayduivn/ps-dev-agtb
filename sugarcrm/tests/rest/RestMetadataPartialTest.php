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

class RestMetadataPartialTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }
    
    /**
     * @group rest
     */
    public function testMetadataGetHashes() {
        $restReply = $this->_restCall('metadata?only_hash=true');

        $this->assertTrue(isset($restReply['reply']['modules']['Accounts']['_hash']),'Account module hash is missing.');
        $this->assertFalse(isset($restReply['reply']['modules']['Accounts']['fields']),'Account module has fields.');
    }
    
    /**
     * @group rest
     */
    public function testMetadataPartialGetModules() {
        // Fetch just the hashes
        $restReply = $this->_restCall('metadata?only_hash=true&type_filter=modules&module_filter=Accounts');
        
        $this->assertTrue(isset($restReply['reply']['modules']['Accounts']['_hash']),'Account module only hash is missing.');
        
        // Call with the same set of hashes that we were sent
        $goodHashes = array('modules' => array('Accounts'=>$restReply['reply']['modules']['Accounts']['_hash']));
        $restReply2 = $this->_restCall('metadata?type_filter=modules&module_filter=Accounts',json_encode($goodHashes));
        
        $this->assertFalse(isset($restReply2['reply']['modules']['Accounts']['fields']),'Account module fields were returned when the hashes matched.');
        
        // Mess up the hashes
        $badHashes = array('modules' => array('Accounts'=>'BAD HASH, NO SOUP FOR YOU'));

        $restReply3 = $this->_restCall('metadata?type_filter=modules&module_filter=Accounts',json_encode($badHashes));
        
        $this->assertTrue(isset($restReply3['reply']['modules']['Accounts']['fields']),'Account module fields were not returned when the hashes didn\'t match.');
        
    }


}
