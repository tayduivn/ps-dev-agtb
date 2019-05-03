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
use Sugarcrm\Sugarcrm\AccessControl\SugarVoter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

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
     * @covers ::vote
     * @covers ::supports
     * @covers ::getSupportedKeys
     * 
     * @dataProvider voteProvider
     */
    public function testVote($notAccessibleList, $module, $entitled, $expected)
    {
        $voter = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getNotAccessibleModuleListByLicenseTypes'])
            ->getMock();

        $voter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $voter->expects($this->any())
            ->method('getNotAccessibleModuleListByLicenseTypes')
            ->will($this->returnValue($notAccessibleList));

        $this->assertSame(
            $expected,
            $voter->vote(AccessControlManager::MODULES_KEY, $module)
        );
    }

    public function voteProvider()
    {
        return [
            [
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
                'BusinessCenters',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
                'BusinessCenters',
                [],
                false,
            ],
            [
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
                'BusinessCenters',
                ['NOT_SERVICE_CLOUD', 'SUGAR_SERVE'],
                true,
            ],
            [
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
                'BusinessCenters',
                ['INVLIAD_SERVICE_CLOUD'],
                true,
            ],
        ];
    }
}