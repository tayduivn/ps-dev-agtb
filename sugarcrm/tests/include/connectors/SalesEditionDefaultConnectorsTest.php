<?php
//FILE SUGARCRM flav=sales ONLY
require_once('include/connectors/ConnectorsTestCase.php');

class SalesEditionConnectorsTest extends Sugar_Connectors_TestCase {

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
        require('custom/modules/connectors/metadata/connectors.php');
        require('custom/modules/connectors/metadata/display_config.php');

        $this->assertEquals(count($default_connectors), 2, "Assert that there are two connectors enabled.");
        $this->assertEquals(count($default_modules_sources), 2, "Assert that there are two modules (Accounts, Contacts) enabled.");
    }

}
?>