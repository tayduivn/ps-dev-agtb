<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


require_once('include/generic/LayoutManager.php');
require_once('include/generic/SugarWidgets/SugarWidgetFieldrelate.php');
require_once('modules/Reports/Report.php');

/**
 * Bug #62040
 * Dashlet Filter for custom Users relationship on custom module empty
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 62040
 */
class Bug62040Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $contact;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->first_name = 'Boro';
        $this->contact->last_name = 'SugarTest';
        $this->contact->save();
    }

    public function testConcatName()
    {
        $layoutDef = array(
            'table' => $this->contact->table_name,
            'input_name0' => array(),
            'source' => 'non-db',
            'name' => 'contacts',
            'rname' => 'last_name',
            'db_concat_fields' => array('first_name', 'last_name'),
            'module' => 'Contacts',
        );
        $html = $this->getSugarWidgetFieldRelate()->displayInput($layoutDef);
        $this->assertContains("Boro SugarTest", $html);
    }

    private function getSugarWidgetFieldRelate()
    {
        $LayoutManager = new LayoutManager();
        $temp = new Report();
        $LayoutManager->setAttributePtr('reporter', $temp);
        $Widget = new SugarWidgetFieldRelate($LayoutManager);
        return $Widget;
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

}
