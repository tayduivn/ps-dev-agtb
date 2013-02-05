<?php
//FILE SUGARCRM flav=pro ONLY
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

/**
 * Bug49982Test.php
 * This test tests that the error message is returned after an upload that exceeds post_max_size
 *
 * @ticket 49982
 */
class Bug49982Test extends Sugar_PHPUnit_Framework_TestCase
{

	var $doc = null;
    var $contract = null;

    public function setUp()
    {
       $_POST = array();
       $_FILES = array();
       $_SERVER['REQUEST_METHOD'] = null;
   	}

    public function tearDown()
    {
        unset($_SERVER['REQUEST_METHOD']);
        $_POST = array();
    }

    /**
     * testUploadSizeError
     * We want to simulate uploading a file that is bigger than the post max size. However the $_FILES global array cannot be overwritten
     * without triggering php errors so we can't trigger the error codes directly.
     * In the scenario we are trying to simulate, the post AND files array are returned empty by php, so let's simulate that
     * in order to test the error message from home page
     */
    function testSaveUploadError() {
        //first lets test that no errors show up under normal conditions, clear out Post array just in case there is stale info
        require_once('include/MVC/View/SugarView.php');
        $sv = new SugarView();
        $this->assertFalse($sv->checkPostMaxSizeError(),'Sugar view indicated an upload error when there should be none.');

        //now lets simulate that we are coming from a post, which along with the empty file and post array should trigger the error message
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue($sv->checkPostMaxSizeError(),'Sugar view list did not return an error, however conditions dictate that an upload with a file exceeding post_max_size has occurred.');
    }

}
