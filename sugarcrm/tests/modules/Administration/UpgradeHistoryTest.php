<?php
require_once 'modules/Administration/UpgradeHistory.php';

class UpgradeHistoryTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function testCheckForExistingSQL()
    {
        $patchToCheck = new stdClass();
        $patchToCheck->name = 'abc';
        $patchToCheck->id = '';
		//BEGIN SUGARCRM flav=ent ONLY
        if ($GLOBALS['db']->dbType == 'oci8') {
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name, date_entered) VALUES
('444', 'abc',to_date('2008-12-20 08:08:20','YYYY-MM-DD hh24:mi:ss'))");
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name , date_entered) VALUES
('555','abc', to_date('2008-12-20 08:08:20','YYYY-MM-DD hh24:mi:ss'))");	
		}
		else {
        //END SUGARCRM flav=ent ONLY
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name, date_entered) VALUES
('444', 'abc','2008-12-20 08:08:20') ");
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name , date_entered) VALUES
('555','abc', '2008-12-20 08:08:20')");
        //BEGIN SUGARCRM flav=ent ONLY    
		}
		//END SUGARCRM flav=ent ONLY
		$uh = new UpgradeHistory();
    	$return = $uh->checkForExisting($patchToCheck);
		$this->assertContains($return->id, array('444','555'));
    	
    	$patchToCheck->id = '555';
    	$return = $uh->checkForExisting($patchToCheck);
    	$this->assertEquals($return->id, '444');
    	
    	$GLOBALS['db']->query("delete from upgrade_history where id='444'");
   		$GLOBALS['db']->query("delete from upgrade_history where id='555'");
    }
}