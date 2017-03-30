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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Notes;

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \Note
 */
class NoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::send_assignment_notifications
     */
    public function testSendAssignmentNotifications()
    {
        $user = $this->createMock('\\User');
        $user->receive_notifications = true;
        $admin = $this->createMock('\\Administration');

        $note = $this->createPartialMock('\\Note', [
            'create_notification_email',
            'getTemplateNameForNotificationEmail',
            'createNotificationEmailTemplate',
        ]);
        $note->email_id = Uuid::uuid1();
        $note->expects($this->never())->method('create_notification_email');
        $note->expects($this->never())->method('getTemplateNameForNotificationEmail');
        $note->expects($this->never())->method('createNotificationEmailTemplate');
        $note->send_assignment_notifications($user, $admin);
    }
}
