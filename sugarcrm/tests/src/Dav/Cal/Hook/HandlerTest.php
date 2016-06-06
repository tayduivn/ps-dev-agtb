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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Hook;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry;
use SugarTestReflection;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as LogicHookHandler;

require_once 'tests/SugarTestCalDavUtilites.php';

/**
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler
 */

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler::getManager
     */
    public function testGetManager()
    {
        $handlerObject = new LogicHookHandler();
        $manager = SugarTestReflection::callProtectedMethod($handlerObject, 'getManager');
        $this->assertInstanceOf('\Sugarcrm\Sugarcrm\JobQueue\Manager\Manager', $manager);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler::getAdapterRegistry
     */
    public function testGetMeetingsAdapterFactory()
    {
        $handlerObject = new LogicHookHandler();
        /* @var $registry Registry */
        $registry = SugarTestReflection::callProtectedMethod($handlerObject, 'getAdapterRegistry');
        $this->assertInstanceOf(
            '\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter\Factory',
            $registry->getFactory('Meetings')
        );
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler::getAdapterRegistry
     */
    public function testGetCallsAdapterFactory()
    {
        $handlerObject = new LogicHookHandler();
        /* @var $registry Registry */
        $registry = SugarTestReflection::callProtectedMethod($handlerObject, 'getAdapterRegistry');
        $this->assertInstanceOf(
            '\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\Factory',
            $registry->getFactory('Calls')
        );
    }
}
