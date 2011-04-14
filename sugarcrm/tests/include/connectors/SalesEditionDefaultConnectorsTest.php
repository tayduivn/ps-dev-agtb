<?php
//FILE SUGARCRM flav=sales ONLY
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');

class SalesEditionConnectorsTest extends Sugar_PHPUnit_Framework_TestCase {

    var $has_custom_connectors_file;
    var $has_custom_display_config_file;
    var $has_custom_accounts_detailviewdefs_file;
    var $has_custom_leads_detailviewdefs_file;
    var $has_custom_contacts_detailviewdefs_file;
    
    function setUp() {
        $this->markTestSkipped("Marked as skipped until we can resolve Hoovers nusoapclient issues.");
  		return;
  		
        if(file_exists('custom/modules/connectors/metadata/connectors.php')) {
           $this->has_custom_connectors_file = true;
           copy('custom/modules/connectors/metadata/connectors.php', 'custom/modules/connectors/metadata/connectors.php.bak');
           unlink('custom/modules/connectors/metadata/connectors.php');
        }
        
        if(file_exists('custom/modules/connectors/metadata/display_config.php')) {
           $this->has_custom_display_config_file = true;
           copy('custom/modules/connectors/metadata/display_config.php', 'custom/modules/connectors/metadata/display_config.php.bak');
           unlink('custom/modules/connectors/metadata/display_config.php');
        }   

        if(file_exists('custom/modules/accounts/metadata/detailviewdefs.php')) {
           $this->has_custom_accounts_detailviewdefs_file = true;
           copy('custom/modules/accounts/metadata/detailviewdefs.php', 'custom/modules/accounts/metadata/detailviewdefs.php.bak');
           unlink('custom/modules/accounts/metadata/detailviewdefs.php');
        } 

        if(file_exists('custom/modules/contactss/metadata/detailviewdefs.php')) {
           $this->has_custom_contacts_detailviewdefs_file = true;
           copy('custom/modules/contacts/metadata/detailviewdefs.php', 'custom/modules/contacts/metadata/detailviewdefs.php.bak');
           unlink('custom/modules/contacts/metadata/detailviewdefs.php');
        } 

        if(file_exists('custom/modules/accounts/metadata/detailviewdefs.php')) {
           $this->has_custom_leads_detailviewdefs_file = true;
           copy('custom/modules/leads/metadata/detailviewdefs.php', 'custom/modules/leads/metadata/detailviewdefs.php.bak');
           unlink('custom/modules/leads/metadata/detailviewdefs.php');
        }         
    }
    
    function tearDown() {
        if($this->has_custom_connectors_file) {
           copy('custom/modules/connectors/metadata/connectors.php.bak', 'custom/modules/connectors/metadata/connectors.php');
           unlink('custom/modules/connectors/metadata/connectors.php.bak');
        }	
        
        if($this->has_custom_display_config_file) {
           copy('custom/modules/connectors/metadata/display_config.php.bak', 'custom/modules/connectors/metadata/display_config.php');
           unlink('custom/modules/connectors/metadata/display_config.php.bak');
        }
        
        if($this->has_custom_accounts_detailviewdefs_file) {
           copy('custom/modules/accounts/metadata/detailviewdefs.php.bak', 'custom/modules/accounts/metadata/detailviewdefs.php');
           unlink('custom/modules/accounts/metadata/detailviewdefs.php.bak');
        }  

        if($this->has_custom_contacts_detailviewdefs_file) {
           copy('custom/modules/contacts/metadata/detailviewdefs.php.bak', 'custom/modules/contacts/metadata/detailviewdefs.php');
           unlink('custom/modules/contacts/metadata/detailviewdefs.php.bak');
        }  

        if($this->has_custom_leads_detailviewdefs_file) {
           copy('custom/modules/leads/metadata/detailviewdefs.php.bak', 'custom/modules/leads/metadata/detailviewdefs.php');
           unlink('custom/modules/leads/metadata/detailviewdefs.php.bak');
        }          
    }
    
    function test_default_connectors() {
        require('modules/Connectors/InstallDefaultConnectors.php');
        $this->assertTrue(file_exists('custom/modules/connectors/metadata/display_config.php'), "Assert custom/modules/connectors/metadata/display_config.php file created.");
        $this->assertTrue(file_exists('custom/modules/connectors/metadata/connectors.php'), "Assert custom/modules/connectors/metadata/connectors.php file created.");
        $this->assertTrue(file_exists('custom/modules/Accounts/metadata/detailviewdefs.php'), "Assert custom/modules/Accounts/metadata/detailviewdefs.php file created.");
        $this->assertTrue(file_exists('custom/modules/Contacts/metadata/detailviewdefs.php'), "Assert custom/modules/Contacts/metadata/detailviewdefs.php file created.");
        
        require('custom/modules/connectors/metadata/connectors.php');
        require('custom/modules/connectors/metadata/display_config.php');
        
        $this->assertEquals(count($default_connectors), 3, "Assert that there are three connectors enabled.");
        $this->assertEquals(count($default_modules_sources), 2, "Assert that there are two modules (Accounts, Contacts) enabled.");
        
        /*
        require('custom/modules/Accounts/metadata/detailviewdefs.php');
        $this->assertTrue(in_array('CONNECTOR', $viewdefs['Accounts']['DetailView']['templateMeta']['form']['buttons']), "Assert that the Get Data button is added to Accounts detailviewdefs.php file.");
        
        $accounts_hover_link_set = false;
        
        foreach($viewdefs['Accounts']['DetailView']['panels'] as $panels) {
        	foreach($panels as $panel) {
        		foreach($panel as $row=>$col) {
        		    if(is_array($col) && $col['name'] == 'name') {
        		       if(isset($col['displayParams']) && count($col['displayParams']['connectors']) == 2) {
                       	  $accounts_hover_link_set = true;  	
        		       }
        		    }
        		}
        	}
        }
        
        $this->assertTrue($accounts_hover_link_set, "Assert that the Accounts hover link is properly set.");
        
        require('custom/modules/Contacts/metadata/detailviewdefs.php');
        $this->assertTrue(in_array('CONNECTOR', $viewdefs['Contacts']['DetailView']['templateMeta']['form']['buttons']), "Assert that the Get Data button is added to Contacts detailviewdefs.php file.");
        
        $contacts_hover_link_set = false;
        
        foreach($viewdefs['Contacts']['DetailView']['panels'] as $panels) {
           foreach($panels as $panel) {
        		foreach($panel as $row=>$col) {
        		    if(is_array($col) && $col['name'] == 'full_name') {
        		       if(isset($col['displayParams']) && count($col['displayParams']['connectors']) == 2) {
                       	  $contacts_hover_link_set = true;  	
        		       }
        		    }
        		}
        	}        	
        }        
               
        $this->assertTrue($contacts_hover_link_set, "Assert that the Contacts hover link is properly set.");
        */ 
    }
    
}  
?>