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
        $id = create_guid();
        $module = 'Users';
        $email = 'email-' . $id . '@example.com';
        $name = 'name-' . $id;

        return array(
            // the organizer adds the invitee to the call
            'returns invite when invitee is added by organizer' => array(
                'inviteesBefore' => array(),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'expected' => array(
                    $id => $module,
                )
            ),
            // the organizer changes the call, the invitee doesn't change the status
            'does not return invite when organiser changes call and invitee has status "none"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            // the invitee changes the status
            'does not return invite when invitee changes status from "none" to "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            'does not return invite when invitee changes status from "none" to "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            'does not return invite when invitee changes status from "none" to "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'tentative',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            'does not return invite when invitee changes status from "accept" to "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'tentative',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            'does not return invite when invitee changes status from "accept" to "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'decline',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            'does not return invite when invitee changes status from "decline" to "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'decline',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            'does not return invite when invitee changes status from "decline" to "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'decline',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'tentative',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            'does not return invite when invitee changes status from "tentative" to "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'tentative',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            'does not return invite when invitee changes status from "tentative" to "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'tentative',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'decline',
                        $name,
                    )
                ),
                'expected' => array()
            ),
            // the organizer changes the call, the invitee has the changed status
            'returns invite when invitee has "accept" status and organizer changes call' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'expected' => array(
                    $id => $module,
                )
            ),
            'returns invite when invitee has "decline" status and organizer changes call' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'decline',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'decline',
                        $name,
                    )
                ),
                'expected' => array(
                    $id => $module,
                )
            ),
            'returns invite when invitee has "tentative" status and organizer changes call' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'tentative',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'tentative',
                        $name,
                    )
                ),
                'expected' => array(
                    $id => $module,
                )
            ),
            // the organizer re-invites the invitee
            'returns invite when organizer re-invites invitee with status "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'expected' => array(
                    $id => $module,
                )
            ),
            'returns invite when organizer re-invites invitee with status "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'decline',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'expected' => array(
                    $id => $module,
                )
            ),
            'returns invite when organizer re-invites invitee with status "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'tentative',
                        $name,
                    )
                ),
                'inviteesAfter' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'expected' => array(
                    $id => $module,
                )
            ),
            // the organiser removes the invitee from the call
            'returns invite when organizer removes invitee with status "none"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'none',
                        $name,
                    )
                ),
                'inviteesAfter' => array(),
                'expected' => array(
                    $id => $module,
                )
            ),
            'returns invite when organizer removes invitee with status "accept"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'accept',
                        $name,
                    )
                ),
                'inviteesAfter' => array(),
                'expected' => array(
                    $id => $module,
                )
            ),
            'returns invite when organizer removes invitee with status "decline"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'decline',
                        $name,
                    )
                ),
                'inviteesAfter' => array(),
                'expected' => array(
                    $id => $module,
                )
            ),
            'returns invite when organizer removes invitee with status "tentative"' => array(
                'inviteesBefore' => array(
                    array(
                        $module,
                        $id,
                        $email,
                        'tentative',
                        $name,
                    )
                ),
                'inviteesAfter' => array(),
                'expected' => array(
                    $id => $module,
                )
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
