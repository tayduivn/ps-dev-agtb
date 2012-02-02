<?php
/*********************************************************************************
* The contents of this file are subject to the SugarCRM Master Subscription
* Agreement ("License") which can be viewed at
* http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
* By installing or using this file, You have unconditionally agreed to the
* terms and conditions of the License, and You may not use this file except in
* compliance with the License. Under the terms of the license, You shall not,
* among other things: 1) sublicense, resell, rent, lease, redistribute, assign
* or otherwise transfer Your rights to the Software, and 2) use the Software
* for timesharing or service bureau purposes such as hosting the Software for
* commercial gain and/or for the benefit of a third party. Use of the Software
* may be subject to applicable fees and any use of the Software without first
* paying applicable fees is strictly prohibited. You do not have the right to
* remove SugarCRM copyrights from the source code or user interface.
*
* All copies of the Covered Code must include on each user interface screen:
* (i) the "Powered by SugarCRM" logo and
* (ii) the SugarCRM copyright notice
* in the same form as they appear in the distribution. See full license for
* requirements.
*
* Your Warranty, Limitations of liability and Indemnity are expressly stated
* in the License. Please refer to the License for the specific language
* governing these rights and limitations under the License. Portions created
* by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/

require_once('modules/Notes/Note.php');

class Bug47069Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() {
        parent::setUp();
        
        $GLOBALS['action'] = 'async';
        $GLOBALS['module'] = 'Administration';
        $GLOBALS['app_strings'] = return_application_language('en_us');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $GLOBALS['mod_strings'] = return_module_language('en_us','Administration');
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        $GLOBALS['current_user'] = new User();
        $GLOBALS['current_user']->retrieve('1');
    }
    
    public function tearDown() {
        unset($GLOBALS['module']);
        unset($GLOBALS['action']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['current_user']);
        unset($_REQUEST);
        $GLOBALS['db']->query("DELETE FROM notes WHERE id IN ('".$this->note1->id."','".$this->note2->id."')");
        // Just in case there is a custom table here
        $GLOBALS['db']->query("DELETE FROM notes_cstm WHERE id_c IN ('".$this->note1->id."','".$this->note2->id."')");
        parent::tearDown();
    }

    public function testRepairXSSNotDuplicating()
    {
        $this->note1 = new Note();
        $this->note1->id = create_guid();
        $this->note1->new_with_id = true;
        $this->note1->name = "[Bug47069] Not deleted Note";
        $this->note1->description = "This note shouldn't be deleted.";
        $this->note1->save();

        $this->note2 = new Note();
        $this->note2->id = create_guid();
        $this->note2->new_with_id = true;
        $this->note2->name = "[Bug47069] Deleted Note";
        $this->note2->description = "This note should be deleted.";
        $this->note2->deleted = 1;
        $this->note2->save();

        ob_start();
        $_REQUEST['adminAction'] = 'refreshEstimate';
        $_REQUEST['bean'] = 'Notes';
        require_once('modules/Administration/Async.php');
        $firstEstimate = $out;
        ob_end_clean();

        ob_start();
        $_REQUEST['adminAction'] = 'repairXssExecute';
        $_REQUEST['bean'] = 'Notes';
        $_REQUEST['id'] = json_encode(array($this->note1->id,$this->note2->id));
        require_once('modules/Administration/Async.php');
        ob_end_clean();

        ob_start();
        $_REQUEST['adminAction'] = 'refreshEstimate';
        $_REQUEST['bean'] = 'Notes';
        require_once('modules/Administration/Async.php');
        $secondEstimate = $out;
        ob_end_clean();

        $this->assertEquals($firstEstimate['count'],$secondEstimate['count'], 'The record count should not increase after a repair XSS');
    }
}