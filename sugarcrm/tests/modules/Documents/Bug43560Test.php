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
require_once('modules/Documents/DocumentSoap.php');

class Bug43560Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $doc = null;
	
	public function setUp()
    {
        global $current_user, $currentModule ;
		$mod_strings = return_module_language($GLOBALS['current_language'], "Documents");
		$current_user = SugarTestUserUtilities::createAnonymousUser();

		$document = new Document();
        $document->document_name = 'Bug 43560 Test Document';
        $document->save();
		$this->doc = $document;
	}
	
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);
        
        $GLOBALS['db']->query("DELETE FROM documents WHERE id = '{$this->doc->id}'");
        unset($this->doc);
    }
	
	function testRevisionSave() {
        $ret = $GLOBALS['db']->query("SELECT COUNT(*) AS rowcount1 FROM document_revisions WHERE document_id = '{$this->doc->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals($row['rowcount1'],0,'We created an empty revision');

        $ret = $GLOBALS['db']->query("SELECT document_revision_id FROM documents WHERE id = '{$this->doc->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertTrue(empty($row['document_revision_id']),'We linked the document to a fake document_revision');
        
        $ds = new DocumentSoap();
        $revision_stuff = array('file' => base64_encode('Pickles has an extravagant beard of pine fur.'), 'filename' => 'a_file_about_pickles.txt', 'id' => $this->doc->id, 'revision' => '1');
        $revisionId = $ds->saveFile($revision_stuff);

        $ret = $GLOBALS['db']->query("SELECT COUNT(*) AS rowcount1 FROM document_revisions WHERE document_id = '{$this->doc->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals($row['rowcount1'],1,'We didn\'t create a revision when we should have');
        
        $ret = $GLOBALS['db']->query("SELECT document_revision_id FROM documents WHERE id = '{$this->doc->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals($row['document_revision_id'],$revisionId,'We didn\'t link the newly created document revision to the document');

        // Double saving doesn't work because save doesn't reset the new_with_id
        $newDoc = new Document();
        $newDoc->retrieve($this->doc->id);

        $newDoc->document_revision_id = $revisionId;
        $newDoc->save(FALSE);

        $ret = $GLOBALS['db']->query("SELECT COUNT(*) AS rowcount1 FROM document_revisions WHERE document_id = '{$this->doc->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals($row['rowcount1'],1,'We didn\'t create a revision when we should have');
        
        $ret = $GLOBALS['db']->query("SELECT document_revision_id FROM documents WHERE id = '{$this->doc->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($ret);
        $this->assertEquals($row['document_revision_id'],$revisionId,'We didn\'t link the newly created document revision to the document');


	}

}
