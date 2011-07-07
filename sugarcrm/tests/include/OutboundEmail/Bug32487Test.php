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
 
require_once('include/OutboundEmail/OutboundEmail.php');

/**
 * @ticket 32487
 */
class Bug32487Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $ib = null;
	var $outbound_id = null;
	
	public function setUp()
    {
        global $current_user, $currentModule ;
		$mod_strings = return_module_language($GLOBALS['current_language'], "Contacts");
		$current_user = SugarTestUserUtilities::createAnonymousUser();
		$this->outbound_id = uniqid();
		$time = date('Y-m-d H:i:s');

		$ib = new InboundEmail();
		$ib->is_personal = 1;
		$ib->name = "Test";
		$ib->port = 3309;
		$ib->mailbox = 'empty';
		$ib->created_by = $current_user->id;
		$ib->email_password = "pass";
		$ib->protocol = 'IMAP';
		$stored_options['outbound_email'] = $this->outbound_id;
	    $ib->stored_options = base64_encode(serialize($stored_options));
	    $ib->save();
	    $this->ib = $ib;
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        
        $GLOBALS['db']->query("DELETE FROM inbound_email WHERE id= '{$this->ib->id}'");
        unset($GLOBALS['mod_strings']);
        unset($this->ib);
    }
    
	function testGetAssoicatedInboundAccountForOutboundAccounts(){
	    global $current_user;
	    $ob = new OutboundEmail();
	    $ob->id = $this->outbound_id;
		
	    $results = $ob->getAssociatedInboundAccounts($current_user);
    	$this->assertEquals($this->ib->id, $results[0], "Could not retrieve the inbound mail accounts for an outbound account");
    	
    	$obEmpty = new OutboundEmail();
    	$obEmpty->id = uniqid();
		
	    $empty_results = $obEmpty->getAssociatedInboundAccounts($current_user);
    	$this->assertEquals(0, count($empty_results), "Outbound email account returned for unspecified/empty inbound mail account.");
    }
}
?>