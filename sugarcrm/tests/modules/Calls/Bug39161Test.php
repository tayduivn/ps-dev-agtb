<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
require_once("modules/ModuleBuilder/parsers/views/GridLayoutMetaDataParser.php");
require_once("modules/ModuleBuilder/parsers/views/ListLayoutMetaDataParser.php");
require_once 'modules/ModuleBuilder/parsers/views/AbstractMetaDataParser.php' ;
require_once 'modules/ModuleBuilder/parsers/views/MetaDataParserInterface.php' ;

class Bug39161Test extends Sugar_PHPUnit_Framework_TestCase
{
    /*
    public function setUp()
    {
        $lv = new ListLayoutMetaDataParser('EditView', 'Calls');
    }
    */
	public function testCallsContactStudioViews()
    {
        $seed = new Call();
		$def = $seed->field_defs['contact_name'];
        $lv = new ListLayoutMetaDataParserMock2(MB_LISTVIEW, 'Calls');
        $this->assertTrue($lv->isValidField($def['name'], $def));
		$this->assertFalse(GridLayoutMetaDataParser::validField($def, 'editview'));
        $this->assertFalse(GridLayoutMetaDataParser::validField($def, 'detailview'));
        $this->assertFalse(GridLayoutMetaDataParser::validField($def, 'quickcreate'));
    }
    
}

class ListLayoutMetaDataParserMock2 extends ListLayoutMetaDataParser
{
    function __construct ($view , $moduleName , $packageName = '')
    {
        $this->view = $view;
    }

}