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

namespace Sugarcrm\Sugarcrm\Dav\Base\Helper;

use Sabre\DAV;
use Sabre\DAVACL;
use Sabre\CalDAV;
use Sugarcrm\Sugarcrm\Dav\Base\Principal;
use Sugarcrm\Sugarcrm\Dav\Cal;
use Sugarcrm\Sugarcrm\Dav\Cal\Schedule;

class ServerHelper
{
    /**
     * Setup DAV Server
     * @return DAV\Server
     */
    public function setUp()
    {
        $principalManager = new Principal\Manager();
        $searchModules = $principalManager->getModulesForSearch();

        $authClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Base\\Auth\\SugarAuth');
        $principalClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Base\\Principal\\SugarPrincipal');
        $calendarClass = \SugarAutoLoader::customClass('\Sugarcrm\\Sugarcrm\\Dav\\Cal\\Backend\\CalendarData');

        $authBackend = new $authClass();
        $principalBackend = new $principalClass();
        $calendarBackend = new $calendarClass();

        $calendarCollection = $principalCollection = array();

        foreach ($searchModules as $module) {
            $principalPath = 'principals/' . strtolower($module);
            $principalCollection[] = new DAVACL\PrincipalCollection($principalBackend, $principalPath);
            $calendarCollection[] = new Cal\CalendarRoot($principalBackend, $calendarBackend, $principalPath);
        }

        $tree = array(
            new Principal\Collection('principals', $principalCollection),
            new DAV\SimpleCollection('calendars', $calendarCollection),
        );

        $server = new DAV\Server($tree);
        $server->setBaseUri($server->getBaseUri());

        $authPlugin = new DAV\Auth\Plugin($authBackend, 'SugarCRM DAV Server');
        $server->addPlugin($authPlugin);

        $aclPlugin = new Principal\Acl\Plugin();
        $aclPlugin->defaultUsernamePath = 'principals/users';
        $server->addPlugin($aclPlugin);

        $caldavPlugin = new Cal\Plugin();
        $server->addPlugin($caldavPlugin);

        /* Calendar scheduling support */
        $schedulePlugin = new Schedule\Plugin();
        $server->addPlugin($schedulePlugin);

        /* WebDAV Sync support */
        $syncPlugin = new DAV\Sync\Plugin();
        $server->addPlugin($syncPlugin);

        return $server;
    }
}
