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
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;
use Sugarcrm\Sugarcrm\AccessControl\SecureObjectInterface;
use Sugarcrm\Sugarcrm\AccessControl\SugarVoter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class SugarVoterTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\SugarVoter
 */
class SugarVoterTest extends TestCase
{
    /**
     * @covers ::getCurrentUserSubscriptions
     *
     * @dataProvider getCurrentUserSubscriptionsProvider
     */
    public function testGetCurrentUserSubscriptions($userLicenseType, $expected)
    {
        $userMock = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLicenseType'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('getLicenseType')
            ->will($this->returnValue([$userLicenseType]));

        global $current_user;
        $current_user = $userMock;
        $voter = new SugarVoter();
        $this->assertSame($expected, TestReflection::callProtectedMethod($voter, 'getCurrentUserSubscriptions', []));
    }

    public function getCurrentUserSubscriptionsProvider()
    {
        return [
            ['SUGAR_SERVE', ['SUGAR_SERVE']],
            ['NOT_SERVICE_CLOUD', []],
        ];
    }

    /**
     * @covers ::getCurrentUserSubscriptions
     *
     * @expectedException \Exception
     */
    public function testGetCurrentUserSubscriptionsException()
    {
        global $current_user;
        $current_user = null;
        $voter = new SugarVoter();
        TestReflection::callProtectedMethod($voter, 'getCurrentUserSubscriptions', []);
    }

    /**
     * @covers ::getProtectedList
     *
     * @dataProvider getProtectedListProvider
     */
    public function testGetProtectedList($key, $expected)
    {
        $access_config = [
            'MODULES' => [
                'BUSINESS_CALENDER' => ['SUGAR_SERVE'],
            ],
            'DASHLETS' => [
                'workbench' => ['SUGAR_SERVE'],
            ],
            'REPORTS' => [
                'protected_report_name' => ['SUGAR_SERVE'],
            ],
            'FIELDS' => [
                'Accounts' => ['field1' => 'SUGAR_SERVE'],
            ],
        ];

        $voterMock = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->getMock();

        TestReflection::setProtectedValue($voterMock, 'access_config', $access_config);
        $this->assertSame($expected, TestReflection::callProtectedMethod($voterMock, 'getProtectedList', [$key]));
    }

    public function getProtectedListProvider()
    {
        return [
            ['MODULES', ['BUSINESS_CALENDER' => ['SUGAR_SERVE']]],
            ['DASHLETS', ['workbench' => ['SUGAR_SERVE']]],
            ['NOT_EXIST', []],
        ];
    }
    /**
     * @covers ::supports
     * @dataProvider supportsProvider
     */
    public function testSupports($access_config, $subject, $expected)
    {
        $voterMock = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProtectedList'])
            ->getMock();

        $voterMock->expects($this->any())
                ->method('getProtectedList')
                ->will($this->returnValue($access_config));

        $this->assertSame($expected, TestReflection::callProtectedMethod($voterMock, 'supports', [[], $subject]));
    }

    public function supportsProvider()
    {
        return [
            [
                ['ACL_MODULE_NAME' => ['SUGAR_SERVE']],
                [AccessControlManager::MODULES_KEY => 'ACL_MODULE_NAME'],
                true,
            ],
            [
                ['ACL_MODULE_NAME' => ['SUGAR_SERVE']],
                [AccessControlManager::MODULES_KEY => 'NOT_SECURE_MODULE'],
                false,
            ],
            [
                ['ACL_MODULE_NAME' => ['SUGAR_SERVE']],
                [AccessControlManager::FIELDS_KEY => ['field' => 'SUGAR_SERVE']],
                false,
            ],
            [
                ['ACL_MODULE_NAME' => [['field1' => 'SUGAR_SERVE'], ['field2' =>'SUGAR_SERVE']]],
                [AccessControlManager::FIELDS_KEY => ['ACL_MODULE_NAME' => 'field1']],
                false,
            ],
            [
                ['ACL_MODULE_NAME' => ['SUGAR_SERVE']],
                [AccessControlManager::FIELDS_KEY => ['field' => 'SUGAR_SERVE']],
                false,
            ],
            [
                ['workbench' => ['SUGAR_SERVE']],
                [AccessControlManager::DASHLETS_KEY => 'workbench'],
                true,
            ],
            [
                ['workbench' => ['SUGAR_SERVE']],
                [AccessControlManager::DASHLETS_KEY => 'NOT_SECURE_DASHLET'],
                false,
            ],
            [
                ['protected_report_name' => ['SUGAR_SERVE']],
                'NOT_ARRAY',
                false,
            ],
            [
                ['protected_report_name' => ['SUGAR_SERVE']],
                ['REPORTS' => 'NOT_SECURE_MODULE', 'MODULES'=>'MORE_THAN_!_COUNT'],
                false,
            ],
        ];
    }

    /**
     * @covers ::voteOnAttribute
     * @dataProvider voteOnAttributeProvider
     */
    public function testVoteOnAttribute($accessConfig, $subject, $entitled, $expected)
    {
        $voter = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $voter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $voter->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($accessConfig));

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame(
            $expected,
            TestReflection::callProtectedMethod($voter, 'voteOnAttribute', ['', $subject, $tokenMock])
        );
    }

    public function voteOnAttributeProvider()
    {
        return [
            [
                ['BUSINESS_CALENDER' => ['SUGAR_SERVE']],
                ['MODULES' => 'BUSINESS_CALENDER'],
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['BUSINESS_CALENDER' => ['SUGAR_SERVE']],
                ['MODULES' => 'BUSINESS_CALENDER'],
                [],
                false,
            ],
            [
                ['BUSINESS_CALENDER' => ['SUGAR_SERVE']],
                ['MODULES' => 'BUSINESS_CALENDER'],
                ['NOT_SERVICE_CLOUD', 'SUGAR_SERVE'],
                true,
            ],
            [
                ['BUSINESS_CALENDER' => ['SUGAR_SERVE']],
                ['MODULES' => 'BUSINESS_CALENDER'],
                ['INVLIAD_SERVICE_CLOUD'],
                false,
            ],
        ];
    }
}
