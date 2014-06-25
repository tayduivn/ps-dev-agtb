<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once "include/SugarWireless/SugarWirelessView.php";

class Bug56845Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
	public function setUp()
	{
        //setup app strings
        global $app_list_strings, $app_strings;
        include "include/language/en_us.lang.php";

	}

	public function tearDown()
	{

	}


    public function testGetMetaDataFileFallbackForDocuments(){
        $view = new SugarWirelessView();
        $file = $view->getMetaDataFileFallback("wireless_basic_search", "Documents");
        $this->assertEquals("include/SugarObjects/templates/file/metadata/searchdefs.php", $file);
    }
}
