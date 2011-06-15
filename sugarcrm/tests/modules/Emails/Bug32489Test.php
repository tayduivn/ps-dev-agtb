<?php 
//FILE SUGARCRM flav!=sales ONLY
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
 
require_once('modules/Emails/Email.php');
require_once('modules/Notes/Note.php');

/**
 * @ticket 32489
 */
class Bug32489Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $em1 = null;
    var $note1 = null;
    var $note2 = null;
    
	var $outbound_id = null;
	
	public function setUp()
    {
        global $current_user, $currentModule,$timedate ;
		$mod_strings = return_module_language($GLOBALS['current_language'], "Contacts");
		$current_user = SugarTestUserUtilities::createAnonymousUser();
		$this->outbound_id = uniqid();
		$time = date('Y-m-d H:i:s');

		$em = new Email();
		$em->name = 'tst_' . uniqid();
		$em->type = 'inbound';
		$em->intent = 'pick';
		$em->date_sent = $timedate->to_display_date_time(gmdate("Y-m-d H:i:s", (gmmktime() + (3600 * 24 * 2) ))) ; //Two days from today 
		$em->save();
	    $this->em1 = $em;
	    
	    $n = new Note();
	    $n->name = 'tst_' . uniqid();
	    $n->filename = 'file_' . uniqid();
	    $n->parent_type = 'Emails';
	    $n->parent_id = $this->em1->id;
	    $n->save();
	    $this->note1 = $n;
	    
	    
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);
        $GLOBALS['db']->query("DELETE FROM emails WHERE id= '{$this->em1->id}'");
        $GLOBALS['db']->query("DELETE FROM notes WHERE id= '{$this->note1->id}'");
        if($this->note2 != null)
	        $GLOBALS['db']->query("DELETE FROM notes WHERE id= '{$this->note2->id}'");
        
        unset($this->em1);
        unset($this->note1);
        unset($this->note2);
    }
    
	function testSimpleImportEmailSearch(){
	    global $current_user,$timedate;
	   
	    //Simple search by name
        $_REQUEST['name'] = $this->em1->name;
	    $results = $this->em1->searchImportedEmails();
		$this->assertEquals(1, count($results['out']), "Could not perform a simple search for imported emails" );
		$this->assertEquals(count($results['out']), $results['totalCount'], "Imported emails search, total count of result set and count query not equal.");
		
		//Search should return nothing
		$_REQUEST['name'] =  uniqid() . uniqid(); //Should be enough entropy.	
		$results = $this->em1->searchImportedEmails();	
		$this->assertEquals(0, count($results['out']), "Could not perform a simple search for imported emails, expected no results" );
		
		//Search by date filters.
		$tomm = gmdate('Y-m-d H:i:s',(gmmktime() + 3600 * 24));
		$tommDisplay = $timedate->to_display_date_time($tomm);
		$_REQUEST['dateFrom'] = $tommDisplay;
		unset($_REQUEST['name']);
		$results = $this->em1->searchImportedEmails();
		$this->assertTrue(count($results['out']) >= 1, "Could not perform a simple search for imported emails with a single date filter" );

		$weekFromNow = gmdate('Y-m-d H:i:s',(gmmktime() + (3600 * 24 * 7)));
		$weekFromNowDisplay = $timedate->to_display_date_time($weekFromNow);
		$_REQUEST['dateTo'] = $weekFromNowDisplay;
		$results = $this->em1->searchImportedEmails();
		$this->assertTrue(count($results['out']) >= 1, "Could not perform a simple search for imported emails with a two date filter" );
    }
    
    function testSimpleImportEmailSearchWithAttachments()
    {
        unset($_REQUEST);
        $_REQUEST['name'] = $this->em1->name;
        $_REQUEST['attachmentsSearch'] = 1;
        $results = $this->em1->searchImportedEmails();	
		$this->assertEquals(1, count($results['out']), "Could not perform a simple search for imported emails with single attachment" );
		
		//Add a second note related to same parent, same results should be obtained.
		$n = new Note();
	    $n->name = 'tst2_' . uniqid();
	    $n->filename = 'file2_' . uniqid();
	    $n->parent_type = 'Emails';
	    $n->parent_id = $this->em1->id;
	    $n->save();
	    $this->note2 = $n;
	    $results = $this->em1->searchImportedEmails();	
		$this->assertEquals(1, count($results['out']), "Could not perform a simple search for imported emails with multiple attachment" );
    }
}
?>