<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'upgrade/scripts/post/9_UpdateFTSSettings.php';

/**
 * Tests for FTS settings upgrade.
 */
class UpgradeFTSSettingsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::mergeModuleList
     * @dataProvider providerTestMergeModuleList
     */
    public function testMergeModuleList(array $oldModuleList, array $newModuleList, array $expected)
    {
        $stub = $this->getClassMock(array('getOldModuleList', 'getNewModuleList'));

        // stub the old module list
        $stub->expects($this->any())
            ->method('getOldModuleList')
            ->will($this->returnValue($oldModuleList));

        // stub the new module list
        $stub->expects($this->any())
            ->method('getNewModuleList')
            ->will($this->returnValue($newModuleList));

        $result = TestReflection::callProtectedMethod($stub, 'mergeModuleList', array());
        $this->assertEquals($expected, $result);
    }

    public function providerTestMergeModuleList()
    {
        return array(
            //same OOB modules
            array(
                array('Accounts' => true, 'Contacts' => true),
                array('Accounts' => true, 'Contacts' => true),
                array(array('Accounts', 'Contacts'), array()),
            ),
            //disabled OOB module
            array(
                array('Accounts' => true, 'Tasks' => false),
                array('Accounts' => true, 'Tasks' => true),
                array(array('Accounts'), array('Tasks')),
            ),
            //disabled custom module
            array(
                array('Accounts' => true, 'Exz_Basic' => false),
                array('Accounts' => true, 'Exz_Basic' => false),
                array(array('Accounts'), array('Exz_Basic')),
            ),
            //enabled custom module
            array(
                array('Accounts' => true, 'Exz_Basic' => true),
                array('Accounts' => true, 'Exz_Basic' => true),
                array(array('Accounts', 'Exz_Basic'), array()),
            ),
            //enabled custom module, but not included in the new enabled modules
            array(
                array('Accounts' => true, 'Exz_Basic' => true),
                array('Accounts' => true, 'Exz_Basic' => false),
                array(array('Accounts', 'Exz_Basic'), array()),
            ),
            //Extra OOB module
            array(
                array('Accounts' => true),
                array('Accounts' => true, 'Employees' => true),
                array(array('Accounts', 'Employees'), array()),
            ),
            //Knowledge Base module enabled
            array(
                array('KBContents' => true),
                array('KBContents' => true),
                array(array('KBContents'), array()),
            ),
            //Knowledge Base module disabled
            array(
                array('KBContents' => false),
                array('KBContents' => false),
                array(array(), array('KBContents')),
            ),
            //Knowledge Base module missing
            array(
                array(),
                array('KBContents' => true),
                array(array('KBContents'), array()),
            ),
        );
    }

    /**
     * @covers ::getOldModuleList
     * @dataProvider providerTestGetOldModuleList
     */
    public function testGetOldModuleList(array $usaModuleList, array $expected)
    {
        $stub = $this->getClassMock(array('getUsaModuleList'));

        // stub the usa module list
        $stub->expects($this->any())
            ->method('getUsaModuleList')
            ->will($this->returnValue($usaModuleList));

        $result = TestReflection::callProtectedMethod($stub, 'getOldModuleList', array());
        $this->assertEquals($expected, $result);
    }

    public function providerTestGetOldModuleList()
    {
        return array(
            //with Knowledge Base module
            array(
                array(
                    'Accounts' => array(
                        'visible' => true,
                    ),
                    'Contacts' => array(
                        'visible' => true,
                    ),
                    'KBDocuments' => array(
                        'visible' => true,
                    ),
                    'Tasks' => array(
                        'visible' => false,
                    ),
                ),
                array(
                    'Accounts' => true,
                    'Contacts' => true,
                    'KBContents' => true,
                    'Tasks' => false,
                ),
            ),
            //without Knowledge Base module
            array(
                array(
                    'Accounts' => array(
                        'visible' => true,
                    ),
                    'Contacts' => array(
                        'visible' => true,
                    ),
                    'Tasks' => array(
                        'visible' => false,
                    ),
                ),
                array(
                    'Accounts' => true,
                    'Contacts' => true,
                    'Tasks' => false,
                ),
            ),
        );
    }

    /**
     * @covers ::getNewModuleList
     * @dataProvider providerTestGetNewModuleList
     */
    public function testGetNewModuleList(array $ftsEnabled, array $ftsDisabled, array $expected)
    {
        $stub = $this->getClassMock(array('getFTSModuleList'));

        // stub the usa module list
        $stub->expects($this->any())
            ->method('getFTSModuleList')
            ->will($this->returnValue(array($ftsEnabled, $ftsDisabled)));

        $result = TestReflection::callProtectedMethod($stub, 'getNewModuleList', array());
        $this->assertEquals($expected, $result);
    }

    public function providerTestGetNewModuleList()
    {
        return array(
            //empty enabled modules
            array(
                array(),
                array('Compaigns', 'Tasks'),
                array('Compaigns' => false, 'Tasks' => false),
            ),
            //empty disabled modules
            array(
                array('Accounts','Contacts','KBContents'),
                array(),
                array('Accounts' => true, 'Contacts' => true, 'KBContents' => true),
            ),
            //empty both
            array(
                array(),
                array(),
                array(),
            ),
            //non-empty both
            array(
                array('Accounts','Contacts','KBContents'),
                array('Tasks'),
                array('Accounts' => true, 'Contacts' => true, 'KBContents' => true, 'Tasks' => false),
            ),
        );
    }

    public function getClassMock(array $methods)
    {
        return  $stub = $this->getMockBuilder('SugarUpgradeUpdateFTSSettings')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

}
