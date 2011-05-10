<?php
//FILE SUGARCRM flav=pro ONLY
class InstallActionsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() 
    {    	  
        //BEGIN SUGARCRM flav=ent ONLY
        if ($GLOBALS['db']->dbType == 'oci8')
            $this->markTestSkipped('Skipping on Oracle; doesn\'t apply to this backend');
        //END SUGARCRM flav=ent ONLY
        
        $admin = new User();
        $GLOBALS['current_user'] = $admin->retrieve('1');
        global $sugar_version, $sugar_flavor;
        global $beanFiles, $beanList, $moduleList, $modListHeader, $sugar_config;
        require('config.php');
        require('include/modules.php');
        $modListHeader = $moduleList;
    	
        $query = "select id from acl_roles where name = 'Tracker'";
        $result = $GLOBALS['db']->query($query);
        $id = $GLOBALS['db']->fetchByAssoc($result);            
        if(!empty($id['id'])) {
           $id = $id['id'];
           $GLOBALS['db']->query("DELETE FROM acl_roles_actions WHERE role_id = '{$id}'");
           $GLOBALS['db']->query("DELETE FROM acl_roles WHERE id = '{$id}'");
           $GLOBALS['db']->query("DELETE FROM acl_roles_users WHERE role_id = '{$id}'");
           $GLOBALS['db']->query("DELETE FROM acl_actions WHERE acltype like 'Tracker%'");
        }
        
        //Call it three times  to simulate the upgrade
        ob_start();
        include('modules/ACL/install_actions.php');
        include('modules/ACL/install_actions.php');
        include('modules/ACL/install_actions.php');     
        ob_end_clean();
    }
    
    public function tearDown() 
    {
        //If it is the ce version, we need to restore db to ce state
        if ($GLOBALS['sugar_flavor'] == 'CE') {
            $query = "delete from acl_actions where acltype like 'Tracker%' and category != 'Trackers'";
            $GLOBALS['db']->query($query);
        
            $query = "select id from acl_roles where name = 'Tracker'";
            $result = $GLOBALS['db']->query($query);
            $role_id = array();
            while ($row = $GLOBALS['db']->fetchByAssoc($result))
                $role_id[] = $row['id'];
        
            if (!empty($role_id)) {
                foreach ($role_id as $id) {
                    $GLOBALS['db']->query("delete from acl_roles_users where role_id = '$id'");
                    $GLOBALS['db']->query("delete from acl_roles_actions where role_id = '$id'");
                    $GLOBALS['db']->query("delete from acl_roles where id = '$id'");
                }
            }
        }
    }
    
    public function testUpgradingFrom451To510() 
    {
        $query = "select count(*) as count from acl_actions where acltype like 'Tracker%'";
        $result = $GLOBALS['db']->query($query);
        $count = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals($count['count'],32);
        
        $query = "select id from acl_roles where name = 'Tracker'";
        $result = $GLOBALS['db']->query($query);
        $id = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertTrue(!empty($id));
        $this->assertEquals(count($id),1);
        
        $query = "select count(role_id) as count from acl_roles_actions where role_id = '{$id['id']}'";
        $result = $GLOBALS['db']->query($query);
        $count = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals($count['count'],32);
        
        $query = "select count(role_id) as count from acl_roles_users where role_id = '{$id['id']}' and user_id = '1'";
        $result = $GLOBALS['db']->query($query);
        $count = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals($count['count'],1);
    }
    
    public function testUpgradingFrom500EntTo510GAEnt() 
    {
        $query = "select count(*) as count from acl_actions where acltype like 'Tracker%'";
        $result = $GLOBALS['db']->query($query);
        $count = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals($count['count'],32);
        
        $query = "select id from acl_roles where name = 'Tracker'";
        $result = $GLOBALS['db']->query($query);
        $id = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertTrue(!empty($id));
        $this->assertEquals(count($id),1);
        
        $query = "select count(role_id) as count from acl_roles_actions where role_id = '{$id['id']}'";
        $result = $GLOBALS['db']->query($query);
        $count = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals($count['count'],32);
        
        $query = "select count(role_id) as count from acl_roles_users where role_id = '{$id['id']}' and user_id = '1'";
        $result = $GLOBALS['db']->query($query);
        $count = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals($count['count'],1);
    }
    
    public function testUpgradingFrom510RcProTo510GaPro()
    {    	
    	$query = "select count(*) as count from acl_actions where acltype = 'Tracker' and category = 'Trackers'";
    	$result = $GLOBALS['db']->query($query);
    	$count = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($count['count'],8);  //Should be 8 with the new entries installed

        $query = "select count(*) as count from acl_actions where acltype = 'TrackerPerf' and category = 'TrackerPerfs'";
    	$result = $GLOBALS['db']->query($query);
    	$count = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($count['count'],8);  //Should be 8 with the new entries installed
		
        $query = "select count(*) as count from acl_actions where acltype = 'TrackerSession' and category = 'TrackerSessions'";
    	$result = $GLOBALS['db']->query($query);
    	$count = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($count['count'],8);  //Should be 8 with the new entries installed
		
        $query = "select count(*) as count from acl_actions where acltype = 'TrackerQuery' and category = 'TrackerQueries'";
    	$result = $GLOBALS['db']->query($query);
    	$count = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($count['count'],8);  //Should be 8 with the new entries installed
		
		$query = "select id from acl_roles where name = 'Tracker'";
		$result = $GLOBALS['db']->query($query);
		$id = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertTrue(!empty($id));
		$this->assertEquals(count($id),1);
		
		$query = "select count(role_id) as count from acl_roles_actions where role_id = '{$id['id']}'";
		$result = $GLOBALS['db']->query($query);
		$count = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($count['count'],32);
		
		$query = "select count(role_id) as count from acl_roles_users where role_id = '{$id['id']}' and user_id = '1'";
		$result = $GLOBALS['db']->query($query);
		$count = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($count['count'],1);
		
    	$query = "select count(*) as count from acl_actions where acltype like 'Tracker%'";
    	$result = $GLOBALS['db']->query($query);
    	$total_count_after = $GLOBALS['db']->fetchByAssoc($result);
    	$this->assertEquals($total_count_after['count'],32);
    }
    
    public function testCeToProFlavorConversion() 
    {
		$query = "select count(*) as count from acl_actions where acltype like 'Tracker%'";
    	$result = $GLOBALS['db']->query($query);
    	$count = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($count['count'],32);  //Should be 32 with the new entries installed

		$query = "select id from acl_roles where name = 'Tracker'";
		$result = $GLOBALS['db']->query($query);
		$id = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertTrue(!empty($id));
		$this->assertEquals(count($id),1);
		
		$query = "select count(role_id) as count from acl_roles_actions where role_id = '{$id['id']}'";
		$result = $GLOBALS['db']->query($query);
		$count = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($count['count'],32);
		
		$query = "select count(role_id) as count from acl_roles_users where role_id = '{$id['id']}' and user_id = '1'";
		$result = $GLOBALS['db']->query($query);
		$count = $GLOBALS['db']->fetchByAssoc($result);
		$this->assertEquals($count['count'],1);
    }
}