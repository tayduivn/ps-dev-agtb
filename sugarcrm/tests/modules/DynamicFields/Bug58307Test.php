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

require_once 'modules/DynamicFields/FieldViewer.php';

class Bug58307Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_fv;
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        
        // Setting the module in the request for this test
        $_REQUEST['view_module'] = 'Accounts';
        
        $this->_fv = new FieldViewer();
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }
    
    public function testPhoneFieldGetsCorrectFieldForm()
    {
        $vardef = array(
            'type' => 'phone',
            'len' => 30,
        );
        
        $layout = $this->_fv->getLayout($vardef);
        
        // Inspect the layout for things we expect. Yes, this is kinda not 
        // scientific but to support varies builds this needs to happen this way.
        $this->assertContains('function forceRange(', $layout, "Layout does not contain expected known function string forceRange");
        $this->assertContains("<input type='text' name='default' id='default' value='' maxlength='30'>", $layout, "Layout does not contain expected known function call");
    }
}