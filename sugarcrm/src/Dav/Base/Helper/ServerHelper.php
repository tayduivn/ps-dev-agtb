<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Dav\Base\Helper;

use Sabre\DAV;
use Sabre\DAVACL;
use Sabre\CalDAV;
use Sugarcrm\Sugarcrm\Dav\Cal\Schedule;

class ServerHelper
{
    public function setUp()
    {
        $authClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Base\\Auth\\SugarAuth');
        $principalClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Base\\Principal\\SugarPrincipal');
        $calendarClass = \SugarAutoLoader::customClass('\Sugarcrm\\Sugarcrm\\Dav\\Cal\\Backend\\CalendarData');

        $authBackend = new $authClass();
        $principalBackend = new $principalClass();
        $calendarBackend = new $calendarClass();

        $tree = array (
            new CalDAV\Principal\Collection($principalBackend),
            new CalDAV\CalendarRoot($principalBackend, $calendarBackend),
        );

        $server = new DAV\Server($tree);
        $server->setBaseUri($server->getBaseUri());

        $authPlugin = new DAV\Auth\Plugin($authBackend, 'SugarCRM DAV Server');
        $server->addPlugin($authPlugin);

        $aclPlugin = new DAVACL\Plugin();
        $server->addPlugin($aclPlugin);

        $caldavPlugin = new CalDAV\Plugin();
        $server->addPlugin($caldavPlugin);

        /* Calendar scheduling support */
        $schedulePlugin = new Schedule\Plugin();
        $server->addPlugin($schedulePlugin);

        return $server;
    }
}
