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
require_once 'modules/Calls/Call.php';

class CallTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Call our call object
     */
    private $callid;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        if(!empty($this->callid)) {
            $GLOBALS['db']->query("DELETE FROM calls WHERE id='{$this->callid}'");
            $GLOBALS['db']->query("DELETE FROM vcals WHERE user_id='{$GLOBALS['current_user']->id}'");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset( $GLOBALS['current_user']);
        unset( $GLOBALS['mod_strings']);
    }

    /**
     * @group bug40999
     */
    public function testCallStatus()
    {
         $call = new Call();
         $this->callid = $call->id = create_guid();
         $call->new_with_id = 1;
         $call->status = 'Test';
         $call->save();
         // then retrieve
         $call = new Call();
         $call->retrieve($this->callid);
         $this->assertEquals('Test', $call->status);
    }

    /**
     * @group bug40999
     */
    public function testCallEmptyStatus()
    {
         $call = new Call();
         $this->callid = $call->id = create_guid();
         $call->new_with_id = 1;
         $call->save();
         // then retrieve
         $call = new Call();
         $call->retrieve($this->callid);
         $this->assertEquals('Planned', $call->status);
    }

    /**
     * @group bug40999
     * Check if empty status is handled correctly
     */
    public function testCallEmptyStatusLang()
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setModString('LBL_DEFAULT_STATUS','FAILED!','Calls');
        $langpack->save();
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Calls');         
        
         $call = new Call();
         $this->callid = $call->id = create_guid();
         $call->new_with_id = 1;
         $call->save();
         // then retrieve
         $call = new Call();
         $call->retrieve($this->callid);
         $this->assertEquals('Planned', $call->status);
    }

    /**
     * @group bug40999
     * Check if empty status is handled correctly
     */
    public function testCallEmptyStatusLangConfig()
    {
         $langpack = new SugarTestLangPackCreator();
         $langpack->setModString('LBL_DEFAULT_STATUS','FAILED!','Calls');
         $langpack->save();
         $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Calls');         
        
         $call = new Call();
         $call->field_defs['status']['default'] = 'My Call';
         $call = new Call();
         $this->callid = $call->id = create_guid();
         $call->new_with_id = 1;
         $call->save();
         // then retrieve
         $call = new Call();
         $call->retrieve($this->callid);
         $this->assertEquals('My Call', $call->status);
    }
}
