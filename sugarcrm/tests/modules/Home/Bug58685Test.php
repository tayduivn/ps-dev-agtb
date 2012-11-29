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
 * Bug58685Test.php
 * This test tests that the error message is returned after an upload that isn't a true upload but just an empty post
 *
 * @ticket 58685
 */
require_once('modules/Home/views/view.list.php');
class Bug58685Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
	public function setUp()
    {
        $this->oldPost = $_POST;
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->oldRM = $_SERVER['REQUEST_METHOD'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $this->oldCL = $_SERVER['CONTENT_LENGTH'];
        }

	}

    public function tearDown()
    {
        $_POST = $this->oldPost ;
        if (isset($this->oldRM)) {
            $_SERVER['REQUEST_METHOD'] = $this->oldRM ;
        }
        if (isset($this->oldCL)) {
            $_SERVER['CONTENT_LENGTH'] = $this->oldCL ;
        }
    }

    /**
     * testEmptyPostError
     */
    function testSaveUploadErrorMessage() {
        //first lets test that no errors show up under normal conditions, clear out Post array just in case there is stale info
        $_POST = array();
        //now lets simulate that we are coming from a post, which along with the empty file and post array should trigger the error message
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_LENGTH'] = 10;
        $view = new HomeViewList();
        $view->processMaxPostErrors();
        $this->expectOutputRegex('/.*Please refresh your page and try again.*/');
    }

}
