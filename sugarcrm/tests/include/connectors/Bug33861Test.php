<?php
//FILE SUGARCRM flav=pro ONLY

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
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');

class Bug33861Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $has_custom_connectors_file;
    var $has_custom_display_config_file;
    var $has_custom_accounts_detailviewdefs_file;
    var $has_custom_leads_detailviewdefs_file;
    var $has_custom_contacts_detailviewdefs_file;

    function setUp() {
    	$this->markTestIncomplete("Marked as skipped until we can resolve Hoovers nusoapclient issues.");
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

    function test_default_connectors() {
        $this->install_connectors();
        if(!file_exists('custom/modules/connectors/metadata/display_config.php')) {
           $this->markTestSkipped('Mark test skipped.  Likely no permission to write to custom directory.');
        }

        $this->assertTrue(file_exists('custom/modules/connectors/metadata/display_config.php'), "Assert custom/modules/connectors/metadata/display_config.php file created.");
        $this->assertTrue(file_exists('custom/modules/connectors/metadata/connectors.php'), "Assert custom/modules/connectors/metadata/connectors.php file created.");
        $this->assertTrue(file_exists('custom/modules/Accounts/metadata/detailviewdefs.php'), "Assert custom/modules/Accounts/metadata/detailviewdefs.php file created.");
        $this->assertTrue(file_exists('custom/modules/Contacts/metadata/detailviewdefs.php'), "Assert custom/modules/Contacts/metadata/detailviewdefs.php file created.");

        require('custom/modules/connectors/metadata/connectors.php');
        require('custom/modules/connectors/metadata/display_config.php');

        $this->assertEquals(count($connectors), 4, "Assert that there are four connectors enabled.");
        $this->assertEquals(count($modules_sources), 3, "Assert that there are two modules (Accounts, Contacts) enabled.");

        $viewdefs = array();

        require('custom/modules/Accounts/metadata/detailviewdefs.php');
        $this->assertTrue(in_array('CONNECTOR', $viewdefs['Accounts']['DetailView']['templateMeta']['form']['buttons']), "Assert that the Get Data button is added to Accounts detailviewdefs.php file.");

        $accounts_hover_link_set = false;

        foreach($viewdefs['Accounts']['DetailView']['panels'] as $panels) {
        	foreach($panels as $panel) {
        		foreach($panel as $row=>$col) {
        		    if(is_array($col) && $col['name'] == 'name') {
        		       if(isset($col['displayParams']) && count($col['displayParams']['connectors']) == 1) {
                       	  $accounts_hover_link_set = true;
        		       }
        		    }
        		}
        	}
        }

        $this->assertTrue($accounts_hover_link_set, "Assert that the Accounts hover link is properly set.");
        /*
        $viewdefs = array();

        require('custom/modules/Contacts/metadata/detailviewdefs.php');
        $this->assertTrue(in_array('CONNECTOR', $viewdefs['Contacts']['DetailView']['templateMeta']['form']['buttons']), "Assert that the Get Data button is added to Contacts detailviewdefs.php file.");

        $contacts_hover_link_set = false;

        foreach($viewdefs['Contacts']['DetailView']['panels'] as $panels) {
           foreach($panels as $panel) {
        		foreach($panel as $row=>$col) {
        		    if(is_array($col) && $col['name'] == 'full_name') {
        		       if(isset($col['displayParams']) && count($col['displayParams']['connectors']) == 1) {
                       	  $contacts_hover_link_set = true;
        		       }
        		    }
        		}
        	}
        }

        $this->assertTrue($contacts_hover_link_set, "Assert that the Contacts hover link is properly set.");
        */
    }

    private function install_connectors() {
		$default_connectors = array (
		  'ext_soap_hoovers' =>
		  array (
		    'id' => 'ext_soap_hoovers',
		    'name' => 'Hoovers&#169;',
		    'enabled' => true,
		    'directory' => 'modules/Connectors/connectors/sources/ext/soap/hoovers',
		    'modules' =>
		    array (
		      0 => 'Accounts',
		      1 => 'Contacts',
		    ),
		  ),
		  'ext_rest_zoominfoperson' =>
		  array (
		    'id' => 'ext_rest_zoominfoperson',
		    'name' => 'Zoominfo&#169; - Person',
		    'enabled' => true,
		    'directory' => 'modules/Connectors/connectors/sources/ext/rest/zoominfoperson',
		    'modules' =>
		    array (
		      0 => 'Accounts',
		      1 => 'Contacts',
		    ),
		  ),
		  'ext_rest_zoominfocompany' =>
		  array (
		    'id' => 'ext_rest_zoominfocompany',
		    'name' => 'Zoominfo&#169; - Company',
		    'enabled' => true,
		    'directory' => 'modules/Connectors/connectors/sources/ext/rest/zoominfocompany',
		    'modules' =>
		    array (
		      0 => 'Accounts',
		    ),
		  ),
		  'ext_rest_linkedin' =>
		  array (
		    'id' => 'ext_rest_linkedin',
		    'name' => 'LinkedIn&#169;',
		    'enabled' => true,
		    'directory' => 'modules/Connectors/connectors/sources/ext/rest/linkedin',
		    'modules' =>
		    array (
		    ),
		  ),
		  /*
		  'ext_rest_crunchbase' =>
		  array (
		    'id' => 'ext_rest_crunchbase',
		    'name' => 'Crunchase&#169;',
		    'enabled' => true,
		    'directory' => 'modules/Connectors/connectors/sources/ext/rest/crunchbase',
		    'modules' =>
		    array (
		    ),
		  ),
		  */
		);


		$default_modules_sources = array (
		  'Accounts' =>
		  array (
		    'ext_soap_hoovers' => 'ext_soap_hoovers',
		    'ext_rest_zoominfoperson' => 'ext_rest_zoominfoperson',
		    'ext_rest_zoominfocompany' => 'ext_rest_zoominfocompany',
		    'ext_rest_linkedin' => 'ext_rest_linkedin',
		    //'ext_rest_crunchbase' => 'ext_rest_crunchbase'
		  ),
		  'Contacts' =>
		  array (
		    'ext_soap_hoovers' => 'ext_soap_hoovers',
		    'ext_rest_zoominfoperson' => 'ext_rest_zoominfoperson',
		    'ext_rest_zoominfocompany' => 'ext_rest_zoominfocompany',
		    'ext_rest_linkedin' => 'ext_rest_linkedin',
		    //'ext_rest_crunchbase' => 'ext_rest_crunchbase'
		  ),
		  'Leads' =>
		  array(
		     'ext_rest_linkedin' => 'ext_rest_linkedin',
		  ),
		);

		if(!file_exists('custom/modules/Connectors/metadata')) {
		   mkdir_recursive('custom/modules/Connectors/metadata');
		}

		if(!write_array_to_file('connectors', $default_connectors, 'custom/modules/Connectors/metadata/connectors.php')) {
		   $GLOBALS['log']->fatal('Cannot write file custom/modules/Connectors/metadata/connectors.php');
		}

		if(!write_array_to_file('modules_sources', $default_modules_sources, 'custom/modules/Connectors/metadata/display_config.php')) {
		   $GLOBALS['log']->fatal('Cannot write file custom/modules/Connectors/metadata/display_config.php');
		}

		require_once('include/connectors/utils/ConnectorUtils.php');
		if(!ConnectorUtils::updateMetaDataFiles()) {
		   $GLOBALS['log']->fatal('Cannot update metadata files for connectors');
		}

    }

}
?>