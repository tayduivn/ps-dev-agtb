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
 * Bug49873Test.php
 * This test is to check the removal of the Contracts relationships in a Document instance.  We test the mark_deleted call
 * on the Document instance to assert that the related Contracts are marked as deleted as well.  Previously, there was no logic
 * to cleanup related Contracts.
 *
 * @author Collin Lee
 */
class Bug49873Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $doc = null;
    var $contract = null;

	public function setUp()
    {
        global $current_user, $currentModule ;
		$mod_strings = return_module_language($GLOBALS['current_language'], "Documents");
		$current_user = SugarTestUserUtilities::createAnonymousUser();
		$this->doc = new Document();
        $this->doc->document_name = 'Bug 49873 Test Document';
        $this->doc->assigned_user_id = $current_user->id;
        $this->doc->save();
        $this->contract = new Contract();
        $this->contract->name = 'Bug 49837 Test Contract';
        $this->contract->assigned_user_id = $current_user->id;
        $this->contract->save();
        $this->doc->load_relationship('contracts');
        $this->doc->contracts->add($this->contract->id);
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);

        $GLOBALS['db']->query("DELETE FROM linked_documents WHERE document_id = '{$this->doc->id}'");
        $GLOBALS['db']->query("DELETE FROM documents WHERE id = '{$this->doc->id}'");
        unset($this->doc);
        $GLOBALS['db']->query("DELETE FROM contracts WHERE id = '{$this->contract->id}'");
        unset($this->contract);
    }

    /**
     * testDocumentMarkDeleted
     * This test will test the call to the mark_deleted function.  This should result in the mark_relationships_deleted function
     * being called in the Documents module.  Then we check that the contracts relationships have been appropriated marked.
     */
	function testDocumentMarkDeleted() {
        $this->doc->load_relationship('contracts');
        $this->assertEquals(1, count($this->doc->contracts));

        //Call mark_deleted
        $this->doc->mark_deleted($this->doc->id);
        $this->doc->save();

        //Now assert that the linked_documents entry (this holds the many-to-many documents to contracts relationship) is marked as deleted
        $deleted = $GLOBALS['db']->getOne($GLOBALS['db']->limitQuerySql("SELECT deleted FROM linked_documents WHERE document_id = '{$this->doc->id}'", 0, 1));
        $this->assertEquals('1', $deleted, 'linked_documents entries are not deleted');
    }

}
