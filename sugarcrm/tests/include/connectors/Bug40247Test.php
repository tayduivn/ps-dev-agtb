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
class Bug40247Test extends Sugar_PHPUnit_Framework_TestCase 
{
    var $has_custom_connectors_file;
    var $has_custom_display_config_file;
    var $has_custom_accounts_detailviewdefs_file;
    var $has_custom_leads_detailviewdefs_file;
    var $has_custom_contacts_detailviewdefs_file;
    
    function setUp() {
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
        
        if(file_exists('custom/modules/Connectors/metadata/mergeviewdefs.php')) {
           unlink('custom/modules/Connectors/metadata/mergeviewdefs.php');
        }
        
        if(file_exists('custom/modules/Connectors/connectors/sources/ext/rest/linkedin/mapping.php')) {
           unlink('custom/modules/Connectors/connectors/sources/ext/rest/linkedin/mapping.php');
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
    
    //BEGIN SUGARCRM flav=pro ONLY
    function test_default_pro_connectors() {
        $this->install_connectors();
        if(!file_exists('custom/modules/connectors/metadata/display_config.php')) {
           $this->markTestSkipped('Mark test skipped.  Likely no permission to write to custom directory.');
        }
        
        $viewdefs = array();
        
        require('modules/Accounts/metadata/detailviewdefs.php');
        $this->assertTrue(in_array('CONNECTOR', $viewdefs['Accounts']['DetailView']['templateMeta']['form']['buttons']), "Assert that the Get Data button is added to Accounts detailviewdefs.php file.");
        
        $twitter_hover_link_set = false;
        $linkedin_hover_link_set = false;
        
        foreach($viewdefs['Accounts']['DetailView']['panels'] as $panels) {
        	foreach($panels as $panel) {
        		foreach($panel as $row=>$col) {
                    if(empty($col))
                    {
                       continue;
                    }

        		    if(is_array($col) && $col['name'] == 'name') {
        		       if(isset($col['displayParams']) && isset($col['displayParams']['connectors'])) {
                       	  foreach($col['displayParams']['connectors'] as $entry)
                       	  {
                       	  	   if($entry == 'ext_rest_linkedin')
                       	  	   {
                       	  	   	 $linkedin_hover_link_set = true;
                       	  	   } else if($entry == 'ext_rest_twitter') {
                       	  	   	 $twitter_hover_link_set = true;
                       	  	   }
                       	  }
        		       }
        		       break;
        		    }
        		}
        	}
        }
        
        $this->assertTrue($twitter_hover_link_set, "Assert that the Twitter hover link is properly set for Accounts.");
        $this->assertTrue($linkedin_hover_link_set, "Assert that the LinkedIn hover link is properly set for Accounts.");

        $person_modules = array ('Contacts', 'Prospects', 'Leads');
        
        foreach($person_modules as $mod)
        {
	        require("modules/{$mod}/metadata/detailviewdefs.php");
	        $twitter_hover_link_set = false;
	        $linkedin_hover_link_set = false;
	        
	        foreach($viewdefs["{$mod}"]['DetailView']['panels'] as $panels) {
	        	foreach($panels as $panel) {
	        		foreach($panel as $row=>$col) {

                        if(empty($col))
                        {
                           continue;
                        }

	        		    if(is_array($col) && $col['name'] == 'full_name') {
	        		       if(isset($col['displayParams']) && isset($col['displayParams']['connectors'])) {
	                       	  foreach($col['displayParams']['connectors'] as $entry)
	                       	  {
								   if($entry == 'ext_rest_twitter') {
	                       	  	   	 $twitter_hover_link_set = true;
	                       	  	   }
	                       	  }
	        		       }
	        		    } else if(is_array($col) && $col['name'] == 'account_name') {
	        		       if(isset($col['displayParams']) && isset($col['displayParams']['connectors'])) {
	                       	  foreach($col['displayParams']['connectors'] as $entry)
	                       	  {
								   if($entry == 'ext_rest_linkedin') {
	                       	  	   	 $linkedin_hover_link_set = true;
	                       	  	   }
	                       	  }
	        		       }        		    
	        			}
	        		}
	        	}
	        }
	        
	        $this->assertTrue($twitter_hover_link_set, "Assert that the Twitter hover link is properly set for {$mod}.");
	        $this->assertTrue($linkedin_hover_link_set, "Assert that the LinkedIn hover link is properly set for {$mod}.");
        }
    }
    //END SUGARCRM flav=pro ONLY
    
    //BEGIN SUGARCRM flav=com ONLY
    function test_default_com_connectors() {
        $this->install_connectors();
        if(!file_exists('custom/modules/connectors/metadata/display_config.php')) {
           $this->markTestSkipped('Mark test skipped.  Likely no permission to write to custom directory.');
        }
        
        $viewdefs = array();
        
        require('modules/Accounts/metadata/detailviewdefs.php');
        $this->assertFalse(in_array('CONNECTOR', $viewdefs['Accounts']['DetailView']['templateMeta']['form']['buttons']), "Assert that the Get Data button is not added to Accounts detailviewdefs.php file.");
        
        $twitter_hover_link_set = false;
        $linkedin_hover_link_set = false;
        
        foreach($viewdefs['Accounts']['DetailView']['panels'] as $panels) {
        	foreach($panels as $panel) {
        		foreach($panel as $row=>$col) {
        		    if(is_array($col) && $col['name'] == 'name') {
        		       if(isset($col['displayParams']) && isset($col['displayParams']['connectors'])) {
                       	  foreach($col['displayParams']['connectors'] as $entry)
                       	  {
                       	  	   if($entry == 'ext_rest_linkedin')
                       	  	   {
                       	  	   	 $linkedin_hover_link_set = true;
                       	  	   } else if($entry == 'ext_rest_twitter') {
                       	  	   	 $twitter_hover_link_set = true;
                       	  	   }
                       	  }
        		       }
        		    }
        		}
        	}
        }
        
        $this->assertFalse($twitter_hover_link_set, "Assert that the Twitter hover link is not set for Accounts.");
        $this->assertTrue($linkedin_hover_link_set, "Assert that the LinkedIn hover link is set for Accounts.");
    	
    }
    //END SUGARCRM flav=com ONLY
    
    private function install_connectors() {
    	require('modules/Connectors/InstallDefaultConnectors.php');
    }
    
}  
?>