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

namespace Sugarcrm\SugarcrmTests\modules\Calendar;

use \CalendarUtils;

/**
 * Class CalendarUtilsCRYS1656Test
 * @package Sugarcrm\SugarcrmTests\modules\Calendar
 * @coversDefaultClass CalendarUtils
 */
class CalendarUtilsCRYS1656Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider for testCompareBeforeAfterInvites.
     *
     * @see CalendarUtilsCRYS1656Test::testCompareBeforeAfterInvites
     */
    public function compareBeforeAfterInvitesProvider()
    {
        $id1 = \Sugarcrm\Sugarcrm\Util\Uuid::uuid4();
        $id2 = \Sugarcrm\Sugarcrm\Util\Uuid::uuid4();
        $module = 'Users';
        $email1 = 'email-' . $id1 . '@example.com';
        $name1 = 'name-' . $id1;
        $name2 = 'name-' . $id2;
        $email2 = 'email-' . $id2 . '@example.com';

        return array(
            // the organizer adds the invitee to the call
            'returns invite when invitee is added by organizer' => array(
                'inviteesBefore' => array(),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    )
                ),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            // the organizer changes the call, the invitee doesn't change the status
            'return invite when organiser changes call and invites status not changed' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                ),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            // the invitee changes the status
            'does not return invite when invitee changes status from "none" to "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                ),
                'expected' => array(),
            ),
            'does not return invite when invitee changes status from "none" to "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                ),
                'expected' => array(),
            ),
            'does not return invite when invitee changes status from "none" to "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'tentative',
                        $name1,
                    ),
                ),
                'expected' => array(),
            ),
            'does not return invite when invitee changes status from "accept" to "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'tentative',
                        $name1,
                    )
                ),
                'expected' => array(),
            ),
            'does not return invite when invitee changes status from "accept" to "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'decline',
                        $name1,
                    ),
                ),
                'expected' => array(),
            ),
            'does not return invite when invitee changes status from "decline" to "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'decline',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                ),
                'expected' => array(),
            ),
            'does not return invite when invitee changes status from "decline" to "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'decline',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'tentative',
                        $name1,
                    ),
                ),
                'expected' => array(),
            ),
            'does not return invite when invitee changes status from "tentative" to "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'tentative',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                ),
                'expected' => array(),
            ),
            'does not return invite when invitee changes status from "tentative" to "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'tentative',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'decline',
                        $name1,
                    ),
                ),
                'expected' => array()
            ),
            // the organizer changes the call, the invitee has the changed status
            'returns invite when invitee has "accept" status and organizer changes call' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                ),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            'returns invite when invitee has "decline" status and organizer changes call' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'decline',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'decline',
                        $name1,
                    ),
                ),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            'returns invite when invitee has "tentative" status and organizer changes call' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'tentative',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'tentative',
                        $name1,
                    ),
                ),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            // the organizer re-invites the invitee
            'returns invite when organizer re-invites invitee with status "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                ),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            'returns invite when organizer re-invites invitee with status "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'decline',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                ),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            'returns invite when organizer re-invites invitee with status "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'tentative',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                ),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            // the organiser removes the invitee from the call
            'returns invite when organizer removes invitee with status "none"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            'returns invite when organizer removes invitee with status "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            'returns invite when organizer removes invitee with status "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'decline',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            'returns invite when organizer removes invitee with status "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'tentative',
                        $name1,
                    ),
                ),
                'inviteesAfter' => array(),
                'expected' => array(
                    $id1 => $module,
                ),
            ),
            'returns invite when one of use change status' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'none',
                        $name1,
                    ),
                    array(
                        $module,
                        $id2,
                        $email2,
                        'none',
                        $name2,
                    ),
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id1,
                        $email1,
                        'accept',
                        $name1,
                    ),
                    array(
                        $module,
                        $id2,
                        $email2,
                        'none',
                        $name2,
                    ),
                ),
                'expected' => array(
                    $id2 => $module,
                ),
            ),
        );
    }

    /**
     * CRYS-1656: iCal: user sends a notification to himself after changing his status in iCal.
     *
     * @covers       CalendarUtils::compareBeforeAfterInvites
     * @dataProvider compareBeforeAfterInvitesProvider
     * @param array $inviteesBefore
     * @param array $inviteesAfter
     * @param array $expected
     */
    public function testCompareBeforeAfterInvites($inviteesBefore, $inviteesAfter, $expected)
    {
        $actual = CalendarUtils::compareBeforeAfterInvites($inviteesBefore, $inviteesAfter);
        $this->assertEquals($expected, $actual);
    }
}
