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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Emails;

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \Email
 */
class EmailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::sendEmail
     * @expectedException \SugarException
     */
    public function testSendEmail_OnlyDraftsCanBeSent()
    {
        $user = $this->createMock('\\User');
        $user->id = Uuid::uuid1();
        $config = new \OutboundEmailConfiguration($user);

        $email = $this->createPartialMock('\\Email', []);
        $email->state = \Email::STATE_ARCHIVED;
        $email->sendEmail($config);
    }

    /**
     * @covers ::getMobileSupportingModules
     */
    public function testGetMobileSupportingModules()
    {
        $actual = \Email::getMobileSupportingModules();

        $expected = [
            'EmailAddresses',
            'EmailParticipants',
            'OutboundEmail',
            'UserSignatures',
        ];
        $this->assertEquals($expected, $actual);
    }

    public function isStateTransitionAllowedProvider()
    {
        return [
            [
                false,
                \Email::STATE_ARCHIVED,
                \Email::STATE_ARCHIVED,
                true,
            ],
            [
                false,
                \Email::STATE_ARCHIVED,
                \Email::STATE_DRAFT,
                true,
            ],
            [
                false,
                \Email::STATE_ARCHIVED,
                \Email::STATE_READY,
                true,
            ],
            [
                false,
                \Email::STATE_ARCHIVED,
                'Foo',
                false,
            ],
            [
                true,
                \Email::STATE_ARCHIVED,
                \Email::STATE_ARCHIVED,
                true,
            ],
            [
                true,
                \Email::STATE_ARCHIVED,
                \Email::STATE_DRAFT,
                false,
            ],
            [
                true,
                \Email::STATE_ARCHIVED,
                \Email::STATE_READY,
                false,
            ],
            [
                true,
                \Email::STATE_ARCHIVED,
                'Foo',
                false,
            ],
            [
                true,
                \Email::STATE_DRAFT,
                \Email::STATE_ARCHIVED,
                false,
            ],
            [
                true,
                \Email::STATE_DRAFT,
                \Email::STATE_DRAFT,
                true,
            ],
            [
                true,
                \Email::STATE_DRAFT,
                \Email::STATE_READY,
                true,
            ],
            [
                true,
                \Email::STATE_DRAFT,
                'Foo',
                false,
            ],
            [
                true,
                \Email::STATE_READY,
                \Email::STATE_ARCHIVED,
                false,
            ],
            [
                true,
                \Email::STATE_READY,
                \Email::STATE_DRAFT,
                false,
            ],
            [
                true,
                \Email::STATE_READY,
                \Email::STATE_READY,
                false,
            ],
            [
                true,
                \Email::STATE_READY,
                'Foo',
                false,
            ],
        ];
    }

    /**
     * @covers ::isStateTransitionAllowed
     * @dataProvider isStateTransitionAllowedProvider
     * @param bool $isUpdate
     * @param string $currentState
     * @param string $newState
     * @param bool $expected
     */
    public function testIsStateTransitionAllowed($isUpdate, $currentState, $newState, $expected)
    {
        $email = $this->createPartialMock('\\Email', ['isUpdate']);
        $email->method('isUpdate')->willReturn($isUpdate);
        $email->state = $currentState;

        $actual = $email->isStateTransitionAllowed($newState);
        $this->assertEquals($expected, $actual);
    }
}
