<?php
//FILE SUGARCRM flav=pro ONLY 
class Bug39280Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testListViewName2Display() {
    	require_once('modules/Teams/metadata/listviewdefs.php');
    	$this->assertContains('related_fields', $listViewDefs['Teams']['NAME'], "Related fields entry is missing");
    	$this->assertContains('name_2', $listViewDefs['Teams']['NAME']['related_fields'], "name_2 fields entry is missing");
	}

}