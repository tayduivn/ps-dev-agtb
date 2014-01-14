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

require_once 'include/connectors/ConnectorsTestCase.php';
require_once 'include/utils.php';
require_once 'include/connectors/sources/default/source.php';

class ConnectorsOriginalMapping extends Sugar_Connectors_TestCase
{
    public function setUp()
    {
        $this->customMappingFile = 'custom/modules/Connectors/connectors/sources/ext/rest/twitter/mapping.php';
        $mapping = array();
        write_array_to_file('mapping', $mapping, $this->customMappingFile);
    }
    public function tearDown()
    {
        unlink($this->customMappingFile);
    }
    public function testOriginalMapping()
    {

        $source = SourceFactory::getSource('ext_rest_twitter');
        $originalMapping = $source->getOriginalMapping();

        // Sets $mapping
        require('modules/Connectors/connectors/sources/ext/rest/twitter/mapping.php');

        $this->assertEquals($mapping, $originalMapping);
    }
}

?>
