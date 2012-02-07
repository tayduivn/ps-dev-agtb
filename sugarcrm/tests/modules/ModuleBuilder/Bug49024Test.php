<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/ModuleBuilder/parsers/relationships/OneToOneRelationship.php' ;
require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php' ;
require_once 'modules/ModuleBuilder/parsers/relationships/UndeployedRelationships.php' ;

class Bug49024Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $objOneToOneRelationship;

    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $this->objOneToOneRelationship = $this->getMockBuilder('OneToOneRelationship')
            ->disableOriginalConstructor()
            ->setMethods(array('getDefinition'))
            ->getMock();

        $this->objOneToOneRelationship->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue(array(
                    'lhs_module' => 'lhs_module',
                    'rhs_module' => 'rhs_module'
                )));

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($this->objOneToOneRelationship);
    }

    public function testDeployedRelationshipsUniqName()
    {
        $objDeployedRelationships = $this->getMockBuilder('DeployedRelationships')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getRelationshipList'))
            ->getMock();

        $objDeployedRelationships->expects($this->any())
            ->method('getRelationshipList')
            ->will($this->returnValue(array()));

        // call private method via reflection

        $method = new ReflectionMethod($objDeployedRelationships, 'getUniqueName');
        $method->setAccessible(TRUE);
        $name = $method->invoke($objDeployedRelationships, $this->objOneToOneRelationship);

        $this->assertEquals('lhs_module_rhs_module_1', $name);
    }

    public function testDeployedRelationshipsUniqName2()
    {
        $objDeployedRelationships = $this->getMockBuilder('DeployedRelationships')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getRelationshipList'))
            ->getMock();

        $objDeployedRelationships->expects($this->any())
            ->method('getRelationshipList')
            ->will($this->returnValue(array(
            'lhs_module_rhs_module_1' => true, 'lhs_module_rhs_module_2' => true
        )));

        // call private method via reflection

        $method = new ReflectionMethod($objDeployedRelationships, 'getUniqueName');
        $method->setAccessible(TRUE);
        $name = $method->invoke($objDeployedRelationships, $this->objOneToOneRelationship);

        $this->assertEquals('lhs_module_rhs_module_3', $name);
    }

    public function testUndeployedRelationshipsUniqName()
    {
        $objUndeployedRelationships = $this->getMockBuilder('UndeployedRelationships')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getRelationshipList'))
            ->getMock();

        $objUndeployedRelationships->expects($this->any())
            ->method('getRelationshipList')
            ->will($this->returnValue(array()));

        // call private method via reflection

        $method = new ReflectionMethod($objUndeployedRelationships, 'getUniqueName');
        $method->setAccessible(TRUE);
        $name = $method->invoke($objUndeployedRelationships, $this->objOneToOneRelationship);

        $this->assertEquals('lhs_module_rhs_module', $name);
    }

    public function testUndeployedRelationshipsUniqName2()
    {
        $objUndeployedRelationships = $this->getMockBuilder('UndeployedRelationships')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getRelationshipList'))
            ->getMock();

        $objUndeployedRelationships->expects($this->any())
            ->method('getRelationshipList')
            ->will($this->returnValue(array(
                'lhs_module_rhs_module' => true, 'lhs_module_rhs_module_1' => true, 'lhs_module_rhs_module_2' => true
            )));

        // call private method via reflection

        $method = new ReflectionMethod($objUndeployedRelationships, 'getUniqueName');
        $method->setAccessible(TRUE);
        $name = $method->invoke($objUndeployedRelationships, $this->objOneToOneRelationship);

        $this->assertEquals('lhs_module_rhs_module_3', $name);
    }
}
?>