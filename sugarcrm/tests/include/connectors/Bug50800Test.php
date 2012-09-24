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
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');

/*
 * This test makes sure that connectors::getConnectors() can handle a badly formed custom metadata file that is either
 * missing the connectors array or the array has been defined as a string
 * @ticket 50800
 */
class Bug50800Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $custom_path = 'custom/modules/Connectors/metadata';
    var $custom_contents;

    function setUp() {
        SugarTestHelper::setUp('app_strings');
        if(file_exists($this->custom_path.'/connectors.php'))
        {
           $this->custom_contents = file_get_contents($this->custom_path.'/connectors.php');
           unlink($this->custom_path.'/connectors.php');
        } else {
            mkdir_recursive($this->custom_path);
        }
    }
    
    function tearDown() {
        //remove connector file
        unlink($this->custom_path.'/connectors.php');

        if(!empty($this->custom_contents))
        {
           file_put_contents($this->custom_path.'/connectors.php', $this->custom_contents);
        }
        SugarTestHelper::tearDown();
    }
    
    function testConnectorFailsStringGracefully()
    {
        //now write a connector file with a string instead of an array for the connector var
        file_put_contents($this->custom_path.'/connectors.php',"<?php\n \$connector = 'Connector String ';");

        //create the connector and call getConnectors
        $cu = new ConnectorUtils();
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $cu->getConnectors(true), 'ConnectorsUtils::getConnectors() failed to return an array when $connectors is a string');
    }

    function testConnectorFailsNullGracefully()
    {
        //now write a connector file with missing array info instead of an array for the connector var
        file_put_contents($this->custom_path.'/connectors.php',"<?php\n ");

        //create the connector and call getConnectors
        $cu = new ConnectorUtils();
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $cu->getConnectors(true), 'ConnectorsUtils::getConnectors() failed to return an array when connectors array was missing. ');
    }
}
