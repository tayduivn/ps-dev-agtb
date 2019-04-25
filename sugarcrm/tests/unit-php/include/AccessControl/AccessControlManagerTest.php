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

namespace Sugarcrm\SugarcrmTestUnit\inc\AccessControl;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\AccessControl\SecureObjectInterface;
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;
use Sugarcrm\Sugarcrm\AccessControl\SecureObjectVoter;
use Sugarcrm\Sugarcrm\AccessControl\SugarFieldVoter;
use Sugarcrm\Sugarcrm\AccessControl\SugarRecordVoter;
use Sugarcrm\Sugarcrm\AccessControl\SugarVoter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AccessControlManagerTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\AccessControlManager
 */
class AccessControlManagerTest extends TestCase
{
    /**
     * @covers ::registerVoters
     * @covers ::registerVoter
     * @covers ::init
     * @covers ::instance
     * @covers ::getRegisteredVoters
     * @covers ::__construct
     *
     */
    public function testRegisterVoters()
    {
        $acm = AccessControlManager::instance();
        $expected = [
            'SugarVoter' => SugarVoter::class,
            'SecureRecordVoter' => SugarRecordVoter::class,
            'SugarFieldVoter' => SugarFieldVoter::class,
            'SecureObjectVoter' => SecureObjectVoter::class,
        ];

        $this->assertSame($expected, TestReflection::getProtectedValue($acm, 'voters'));
        $this->assertNotEmpty(TestReflection::getProtectedValue($acm, 'accessDecisionMgr'));
        $voters = TestReflection::callProtectedMethod($acm, 'getRegisteredVoters', []);
        foreach ($voters as $voter) {
            $this->assertTrue($voter instanceof SecureObjectVoter || $voter instanceof SugarVoter);
        }
    }

    /**
     * @covers ::allowAccess
     * @dataProvider allowAccessProvider
     */
    public function testAllowAccess($access_config, $subject, $entitled, $expected)
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sugarVoter = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $sugarVoter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $sugarVoter->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($access_config));

        $secureObjVoter = $this->getMockBuilder(SecureObjectVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions'])
            ->getMock();

        $secureObjVoter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $acmMock = $this->getMockBuilder(AccessControlManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegisteredVoters', 'getUserToken'])
            ->getMock();

        $acmMock->expects($this->any())
            ->method('getRegisteredVoters')
            ->will($this->returnValue([$secureObjVoter, $sugarVoter]));

        $acmMock->expects($this->any())
            ->method('getUserToken')
            ->will($this->returnValue($tokenMock));

        TestReflection::callProtectedMethod($acmMock, 'init', []);

        $this->assertSame($expected, TestReflection::callProtectedMethod($acmMock, 'allowAccess', [$subject]));
    }

    public function allowAccessProvider()
    {
        return [
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                [AccessControlManager::MODULES_KEY => 'MODULE_NAME'],
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                [accessControlManager::MODULES_KEY => 'MODULE_NAME'],
                [],
                false,
            ],
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                [accessControlManager::MODULES_KEY => 'MODULE_NAME'],
                ['NOT_SERVICE_CLOUD', 'SUGAR_SERVE'],
                true,
            ],
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                [accessControlManager::MODULES_KEY => 'MODULE_NAME'],
                ['INVLIAD_SERVICE_CLOUD'],
                false,
            ],
            [
                ['ACL_MODULE_NAME' => [['field1' => 'SUGAR_SERVE'], ['field2' =>'SUGAR_SERVE']]],
                [AccessControlManager::FIELDS_KEY => ['ACL_MODULE_NAME' => 'field1']],
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                $this->getSecureObejectMock(['SUGAR_SERVE']),
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                $this->getSecureObejectMock(['SUGAR_SERVE']),
                ['INVLIAD_SERVICE_CLOUD'],
                false,
            ],
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                $this->getSecureObejectMock([]),
                ['INVLIAD_SERVICE_CLOUD'],
                false,
            ],
        ];
    }

    /**
     * @covers ::allowModuleAccess
     * @covers ::allowDashletAccess
     *
     * @dataProvider allowModuleAccessProvider
     */
    public function testAllowModuleAccess($access_config, $subject, $entitled, $expected)
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sugarVoter = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $sugarVoter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $sugarVoter->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($access_config));

        $acmMock = $this->getMockBuilder(AccessControlManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegisteredVoters', 'getUserToken'])
            ->getMock();

        $acmMock->expects($this->any())
            ->method('getRegisteredVoters')
            ->will($this->returnValue([$sugarVoter]));

        $acmMock->expects($this->any())
            ->method('getUserToken')
            ->will($this->returnValue($tokenMock));

        TestReflection::callProtectedMethod($acmMock, 'init', []);

        $this->assertSame($expected, $acmMock->allowModuleAccess($subject));
        $this->assertSame($expected, $acmMock->allowDashletAccess($subject));
    }

    public function allowModuleAccessProvider()
    {
        return [
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                'MODULE_NAME',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                'MODULE_NAME',
                [],
                false,
            ],
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                'MODULE_NAME',
                ['NOT_SERVICE_CLOUD', 'SUGAR_SERVE'],
                true,
            ],
            [
                ['MODULE_NAME' => ['SUGAR_SERVE']],
                'MODULE_NAME',
                ['INVLIAD_SERVICE_CLOUD'],
                false,
            ],
        ];
    }

    /**
     * @covers ::allowFieldAccess
     * @covers ::allowRecordAccess
     *
     * @dataProvider allowFieldAccessProvider
     */
    public function testAllowFieldAccess($access_config, $module, $field, $entitled, $expected)
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sugarVoter = $this->getMockBuilder(SugarFieldVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $sugarVoter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $sugarVoter->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($access_config));

        $acmMock = $this->getMockBuilder(AccessControlManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegisteredVoters', 'getUserToken'])
            ->getMock();

        $acmMock->expects($this->any())
            ->method('getRegisteredVoters')
            ->will($this->returnValue([$sugarVoter]));

        $acmMock->expects($this->any())
            ->method('getUserToken')
            ->will($this->returnValue($tokenMock));

        TestReflection::callProtectedMethod($acmMock, 'init', []);

        $this->assertSame($expected, $acmMock->allowFieldAccess($module, $field));
    }

    public function allowFieldAccessProvider()
    {
        return [
            [
                ['MODULE1' => ['field1' => ['SUGAR_SERVE']]],
                'MODULE1',
                'field1',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['MODULE1' => ['field1' => ['SUGAR_SERVE']]],
                'MODULE1',
                'field1_no_in_the_list',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['MODULE1' => ['field1' => ['SUGAR_SERVE']]],
                'MODULE1',
                'field1',
                ['NOT_SERVICE_CCLOUD'],
                false,
            ],
        ];
    }

    protected function getSecureObejectMock(array $allowed)
    {
        $mock = $this->getMockBuilder(SecureObjectInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['allowAccess', 'getCurrentUserSubscriptions'])
            ->getMock();

        $mock->expects($this->any())
            ->method('allowAccess')
            ->will($this->returnValue($allowed));

        return $mock;
    }
}
