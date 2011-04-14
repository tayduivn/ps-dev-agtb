<?php
// FILE SUGARCRM flav=pro ONLY 

class Bug40704Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function testUserColumnNotSortable()
	{
		require_once('modules/ProspectLists/metadata/listviewdefs.php');
		if(!empty($listViewDefs['ProspectLists']['ASSIGNED_USER_NAME'])){
			$this->assertTrue(empty($listViewDefs['ProspectLists']['ASSIGNED_USER_NAME']['sortable']), "User column should not be sortable");
		}
	}
}