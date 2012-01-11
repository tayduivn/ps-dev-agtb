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
 
require_once 'include/database/DBManagerFactory.php';

class RepairDatabaseTest extends Sugar_PHPUnit_Framework_TestCase
{

var $db;	
	
public function setUp()
{
	
	$this->markTestIncomplete('Skip for now');	
    $this->db = DBManagerFactory::getInstance();	
    if($this->db->dbType == 'mysql')
    {
       $sql =  'ALTER TABLE meetings ALTER COLUMN status SET DEFAULT NULL';
       $sql2 = 'ALTER TABLE calls ALTER COLUMN status SET DEFAULT NULL';
       $sql3 = 'ALTER TABLE tasks ALTER COLUMN status SET DEFAULT NULL';

	   //Run the SQL
	   $this->db->query($sql);  
	   $this->db->query($sql2);  
	   $this->db->query($sql3);       
    }
    
         
}	

public function tearDown()
{
	if($this->db->dbType == 'mysql')
    {	
    	$sql = "ALTER TABLE meetings ALTER COLUMN status SET DEFAULT 'Planned'";
    	$sql2 = "ALTER TABLE calls ALTER COLUMN status SET DEFAULT 'Planned'";
    	$sql3 = "ALTER TABLE tasks ALTER COLUMN status SET DEFAULT 'Not Started'";
	    //Run the SQL
	    $this->db->query($sql);
	    $this->db->query($sql2); 
	    $this->db->query($sql3);      	
    }   	
}

public function testRepairTableParams()
{
	    if($this->db->dbType != 'mysql')
	    {
	       $this->markTestSkipped('Skip if not mysql db');
	       return;	
	    }
	
	    $bean = new Meeting();
	    $result = $this->getRepairTableParamsResult($bean);
	    $this->assertRegExp('/ALTER TABLE meetings\s+?modify column status varchar\(100\)  DEFAULT \'Planned\' NULL/i', $result);
	    
	    /*
	    $bean = new Call();
	    $result = $this->getRepairTableParamsResult($bean);
	    $this->assertTrue(!empty($result));
	    $this->assertRegExp('/ALTER TABLE calls\s+?modify column status varchar\(100\)  DEFAULT \'Planned\' NULL/i', $result);
	    */
	    
	    $bean = new Task();
	    $result = $this->getRepairTableParamsResult($bean);
	    $this->assertTrue(!empty($result));	    
	    $this->assertRegExp('/ALTER TABLE tasks\s+?modify column status varchar\(100\)  DEFAULT \'Not Started\' NULL/i', $result);
 
}

private function getRepairTableParamsResult($bean)
{
        $indices   = $bean->getIndices();
        $fielddefs = $bean->getFieldDefinitions();
        $tablename = $bean->getTableName();

		//Clean the indicies to prevent duplicate definitions
		$new_indices = array();
		foreach($indices as $ind_def)
		{
			$new_indices[$ind_def['name']] = $ind_def;
		}
		
        global $dictionary;
        $engine=null;
        if (isset($dictionary[$bean->getObjectName()]['engine']) && !empty($dictionary[$bean->getObjectName()]['engine']) )
        {
            $engine = $dictionary[$bean->getObjectName()]['engine'];	
        }
        
        
	    $result = $this->db->repairTableParams($bean->table_name, $fielddefs, $new_indices, false, $engine);
	    return $result;	
}
	
}