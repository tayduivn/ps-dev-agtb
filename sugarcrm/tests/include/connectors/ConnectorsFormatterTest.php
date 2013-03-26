<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA") which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'include/connectors/formatters/FormatterFactory.php';
require_once 'include/MVC/Controller/SugarController.php';
require_once 'include/connectors/ConnectorsTestCase.php';

class ConnectorsFormatterTest extends Sugar_Connectors_TestCase
{
    public $parentFieldArray;
    public $vardef;
    public $displayParams;
    public $tabindex;
    public $ss;

    public function setUp()
    {
        //Store original files
        if (!file_exists(CONNECTOR_DISPLAY_CONFIG_FILE)) {
            $the_string = <<<EOQ
<?php
\$modules_sources = array (
  'Accounts' =>
  array (
    'ext_rest_linkedin' => 'ext_rest_linkedin',
  ),
  'Opportunities' =>
  array (
    'ext_rest_linkedin' => 'ext_rest_linkedin',
  ),
  'Contacts' =>
  array (
  ),
);

EOQ;
            SugarAutoLoader::put(CONNECTOR_DISPLAY_CONFIG_FILE, $the_string, true);
        }
        parent::setUp();

        if (file_exists('custom/modules/Connectors/connectors/sources/ext/rest/twitter/twitter.php')) {
            copy_recursive('custom/modules/Connectors/connectors/sources/ext/rest/twitter', 'custom/modules/Connectors/backup/connectors/sources/ext/rest/twitter_backup');
            ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/twitter');
            SugarAutoLoader::delFromMap('custom/modules/Connectors/backup/sources/ext/rest/twitter');
        }

        if (file_exists('custom/modules/Connectors/connectors/sources/ext/rest/linkedin/linkedin.php')) {
            copy_recursive('custom/modules/Connectors/connectors/sources/ext/rest/linkedin', 'custom/modules/Connectors/backup/connectors/sources/ext/rest/linkedin_backup');
            ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/linkedin');
            SugarAutoLoader::delFromMap('custom/modules/Connectors/backup/sources/ext/rest/linkedin');
        }

           //Setup the neccessary Smarty configurations
        $this->parentFieldArray = 'fields';
        require_once 'include/SugarObjects/VardefManager.php';
        VardefManager::loadVardef('Accounts', 'Account', true);
        require_once 'cache/modules/Accounts/Accountvardefs.php';
        $this->vardef = $GLOBALS['dictionary']['Account']['fields']['name'];
        $this->displayParams = array('sources'=>array('ext_rest_linkedin','ext_rest_twitter'));
        $this->tabindex = 0;
        require_once 'include/Sugar_Smarty.php';
        $this->ss = new Sugar_Smarty();
        $this->ss->assign('parentFieldArray', $this->parentFieldArray);
        $this->ss->assign('vardef', $this->vardef);
        $this->ss->assign('displayParams', $this->displayParams);
        $this->ss->left_delimiter = '{{';
        $this->ss->right_delimiter = '}}';

        //Setup the mapping to guarantee that we have hover fields for the Accounts module
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;
        $_REQUEST['modify'] = true;
        $_REQUEST['action'] = 'SaveModifyMapping';
        $_REQUEST['mapping_values'] = '';
        $_REQUEST['mapping_sources'] = 'ext_rest_linkedin,ext_rest_twitter';

        $controller = new ConnectorsController();
        $controller->action_SaveModifyMapping();

        FormatterFactory::$formatter_map = array();
        ConnectorFactory::$source_map = array();
    }

    public function tearDown()
    {
        parent::tearDown();
        if (file_exists('custom/modules/Connectors/connectors/sources/ext/rest/twitter_backup/twitter.php')) {
            copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/rest/twitter_backup', 'custom/modules/Connectors/connectors/sources/ext/rest/twitter');
            ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/twitter_backup');
        }

        if (file_exists('custom/modules/Connectors/connectors/sources/ext/rest/linkedin_backup/linkedin.php')) {
            copy_recursive('custom/modules/Connectors/backup/connectors/sources/ext/rest/linkedin_backup', 'custom/modules/Connectors/connectors/sources/ext/rest/linkedin');
            ConnectorsTestUtility::rmdirr('custom/modules/Connectors/backup/sources/ext/rest/linkedin_backup');
        }
    }

    public function testHoverLinkForAccounts()
    {
        $enabled_sources = ConnectorUtils::getModuleConnectors('Accounts');
        $hover_sources = array();
        $displayParams = array();
        $displayParams['module'] = 'Accounts';
        $displayParams['enableConnectors'] = true;

        foreach ($enabled_sources as $id => $mapping) {
            $source = SourceFactory::getSource($id);
            if ($source->isEnabledInHover()) {
                $parts = preg_split('/_/', $id);
                $hover_sources[$parts[count($parts) - 1]] = $id;
                $displayParams['connectors'][] = $id;
            }
        }

        if (!empty($hover_sources)) {
            $output = ConnectorUtils::getConnectorButtonScript($displayParams, $this->ss);
            preg_match_all('/<div[^\>]*?>/', $output, $matches);
            $this->assertTrue(isset($matches[0]));
        }
    }

    public function testRemoveHoverLinksInViewDefs()
    {
        $module = 'Accounts';

        if (file_exists("custom/modules/{$module}/metadata/detailviewdefs.php")) {
            require("custom/modules/{$module}/metadata/detailviewdefs.php");
        } elseif (file_exists("modules/{$module}/metadata/detailviewdefs.php")) {
            require("modules/{$module}/metadata/detailviewdefs.php");
        }

        $this->assertTrue(!empty($viewdefs));

        //Remove hover fields
        ConnectorUtils::removeHoverField($viewdefs, $module);
        $foundHover = false;
        foreach ($viewdefs[$module]['DetailView']['panels'] as $panel_id => $panel) {
            foreach ($panel as $row_id => $row) {
                foreach ($row as $field_id => $field) {
                    if (is_array($field) && !empty($field['displayParams']['enableConnectors'])) {
                        $foundHover = true;
                    }
                } //foreach
            } //foreach
        } //foreach

        //There should have been no hover fields found
        $this->assertTrue(!$foundHover);
    }

    public function testModifyDisplayChanges()
    {
        $module = 'Accounts';

        //Now call the code that will add the mapping fields
        $_REQUEST['display_values'] = "ext_rest_linkedin:Accounts";
        $_REQUEST['display_sources'] = "ext_rest_linkedin,ext_rest_twitter";
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;

           $controller = new ConnectorsController();
        $controller->action_SaveModifyDisplay();

        if (file_exists("custom/modules/{$module}/metadata/detailviewdefs.php")) {
            require("custom/modules/{$module}/metadata/detailviewdefs.php");
            foreach ($viewdefs[$module]['DetailView']['panels'] as $panel_id => $panel) {
                foreach ($panel as $row_id => $row) {
                    foreach ($row as $field_id => $field) {
                        $name = is_array($field) ? $field['name'] : $field;
                        switch (strtolower($name)) {
                            case "account_name":
                                $this->assertTrue(!empty($field['displayParams']['enableConnectors']));
                                $this->assertTrue(in_array('ext_rest_linkedin', $field['displayParams']['connectors']));
                                $this->assertTrue(in_array('ext_rest_twitter', $field['displayParams']['connectors']));
                                break;
                        }
                    } //foreach
                } //foreach
            } //foreach

            // Call remove again b/c we know for sure there are now fields.
            $this->testRemoveHoverLinksInViewDefs();
        } else {
            // Failed because we couldn't create the custom file.
            $this->assertTrue(false);
        }
    }
}
