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

require_once 'include/connectors/ConnectorFactory.php';
require_once 'include/connectors/sources/SourceFactory.php';
require_once 'include/connectors/ConnectorsTestUtility.php';
require_once 'include/connectors/utils/ConnectorUtils.php';
require_once 'modules/Connectors/controller.php';

class Sugar_Connectors_TestCase extends Sugar_PHPUnit_Framework_TestCase
{
    public $original_modules_sources;
    public $original_searchdefs;
    public $original_connectors;

    public function setUp()
    {
        ConnectorUtils::getDisplayConfig();
        require(CONNECTOR_DISPLAY_CONFIG_FILE);
        $this->original_modules_sources = $modules_sources;

        //Remove the current file and rebuild with default
        SugarAutoLoader::unlink(CONNECTOR_DISPLAY_CONFIG_FILE);
        $this->original_searchdefs = ConnectorUtils::getSearchDefs(true);

        $this->original_connectors = ConnectorUtils::getConnectors(true);
    }

    public function tearDown()
    {
        if ($this->original_modules_sources != null) {
            write_array_to_file('modules_sources', $this->original_modules_sources, CONNECTOR_DISPLAY_CONFIG_FILE);
        }
        if ($this->original_searchdefs != null) {
            write_array_to_file('searchdefs', $this->original_searchdefs, 'custom/modules/Connectors/metadata/searchdefs.php');
        }
        if ($this->original_connectors != null) {
            ConnectorUtils::saveConnectors($this->original_connectors);
        }
    }
}
