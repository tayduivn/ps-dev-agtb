<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'include/connectors/utils/ConnectorUtils.php';

class ConnectorUtilsFileTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->timestamp = time();
        $this->test_path = 'custom/modules/Connectors/connectors/sources/test' . $this->timestamp;
    }

    public function tearDown()
    {
        rmdir_recursive($this->test_path);
    }
    public function testSetConnectorStrings()
    {
        $success = ConnectorUtils::setConnectorStrings(
            'test' . $this->timestamp,
            array('asdf' => 'jkl;'),
            'asdf'
        );

        $this->assertTrue($success);
        $this->assertFileExists($this->test_path . '/language/asdf.lang.php');
    }
}
