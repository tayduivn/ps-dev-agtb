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
        $user = \BeanFactory::newBean('Users');
        $user->id = Uuid::uuid1();
        $config = new \OutboundEmailConfiguration($user);

        $email = \BeanFactory::newBean('Emails');
        $email->state = \Email::EMAIL_STATE_ARCHIVED;
        $email->sendEmail($config);
    }
}
