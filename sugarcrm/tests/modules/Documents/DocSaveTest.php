<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/Documents/Document.php';

class DocSaveTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Document instance to run tests with.
     * @var Document
     */
    public $doc = null;

    protected function setUp()
    {
        global $current_user, $currentModule;
        $mod_strings = return_module_language($GLOBALS['current_language'], "Documents");
        $current_user = SugarTestUserUtilities::createAnonymousUser();

        $document = new Document();
        $document->name = 'Test Document';
        $document->save();
        $this->doc = $document;
    }

    protected function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);

        $GLOBALS['db']->query("DELETE FROM documents WHERE id = '{$this->doc->id}'");
        unset($this->doc);
    }

    public function testDocTypeSaveDefault()
    {
        // Assert doc type default is 'Sugar'
        $this->assertEquals($this->doc->doc_type, 'Sugar');
    }

    public function testDocTypeSaveDefaultInDb()
    {
        $query = "SELECT * FROM documents WHERE id = '{$this->doc->id}'";
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            // Assert doc type default is 'Sugar'
            $this->assertEquals($row['doc_type'], 'Sugar');

        }
    }

}
