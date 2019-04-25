<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\data;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;

/**
 * @coversDefaultClass \SugarACL
 *
 */
class SugarACLTest extends TestCase
{
    /**
     * @covers SugarACL::checkAccess
     *
     * @dataProvider checkAccessProvider
     */
    public function testCheckAccess($aclAllowed, $licenseTypeAllowed, $expected)
    {
        $acmMock = $this->getMockBuilder(AccessControlManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['allowModuleAccess'])
            ->getMock();

        $acmMock->expects($this->any())
            ->method('allowModuleAccess')
            ->will($this->returnValue($licenseTypeAllowed));

        $acl1 = $this->createMock('SugarACLStatic');
        $acl1->expects($this->exactly(1))->method('checkAccess')->with('test', 'test2')->will($this->returnValue($aclAllowed));
        \SugarACL::$acls['test'] = array($acl1);
        \SugarACL::$accessControlMgr = $acmMock;
        $this->assertSame($expected, \SugarACL::checkAccess('test', 'test2'));
    }

    public function checkAccessProvider()
    {
        return [
            [true, true, true],
            [false, true, false],
            [true, false, false],
        ];
    }
}
