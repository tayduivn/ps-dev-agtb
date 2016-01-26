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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Schedule;

use Sabre\DAVServerTest as DavServerTest;
use Sabre\CalDAV\Backend\MockScheduling;
use Sugarcrm\Sugarcrm\Dav\Cal\Schedule\Plugin as SchedulePlugin;

class DAVServerMock extends DavServerTest
{
    public function setUp()
    {
        $this->caldavBackend = new MockScheduling($this->caldavCalendars, $this->caldavCalendarObjects);

        $this->setupCalDAVScheduling = false;
        parent::setUp();
        $this->setupCalDAVScheduling = true;

        if ($this->setupCalDAVScheduling) {
            $this->caldavSchedulePlugin = new SchedulePlugin();
            $this->server->addPlugin($this->caldavSchedulePlugin);
        }

        $aclPlugin = $this->server->getPlugin('acl');
        $aclPlugin->defaultUsernamePath = 'principals';

        $this->principalBackend->principals = array(
            array(
                'uri' => 'principals/user1',
                '{DAV:}displayname' => 'User 1',
                '{http://sabredav.org/ns}email-address' => 'user1.sabredav@sabredav.org',
                '{http://sabredav.org/ns}vcard-url' => 'addressbooks/user1/book1/vcard1.vcf',
            ),
            array(
                'uri' => 'principals/admin',
                '{DAV:}displayname' => 'Admin',
            ),
            array(
                'uri' => 'principals/user2',
                '{DAV:}displayname' => 'User 2',
                '{http://sabredav.org/ns}email-address' => 'user2.sabredav@sabredav.org',
            ),
        );
    }

    /**
     * Mock method for event delivering
     * @param string $oldObject
     * @param string $newObject
     * @param bool|false $disableScheduling
     */
    public function deliver($oldObject, &$newObject, $disableScheduling = false)
    {

        $this->server->httpRequest->setUrl($this->calendarObjectUri);
        if ($disableScheduling) {
            $this->server->httpRequest->setHeader('Schedule-Reply', 'F');
        }

        if ($oldObject && $newObject) {
            $this->putPath($this->calendarObjectUri, $oldObject);

            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $newObject);
            rewind($stream);
            $modified = false;

            $this->server->emit('beforeWriteContent', [
                $this->calendarObjectUri,
                $this->server->tree->getNodeForPath($this->calendarObjectUri),
                &$stream,
                &$modified
            ]);
            if ($modified) {
                $newObject = $stream;
            }

        } elseif ($oldObject && !$newObject) {
            $this->putPath($this->calendarObjectUri, $oldObject);

            $this->caldavSchedulePlugin->beforeUnbind(
                $this->calendarObjectUri
            );
        } else {
            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $newObject);
            rewind($stream);
            $modified = false;
            $this->server->emit('beforeCreateFile', array(
                $this->calendarObjectUri,
                &$stream,
                $this->server->tree->getNodeForPath(dirname($this->calendarObjectUri)),
                &$modified
            ));

            if ($modified) {
                $newObject = $stream;
            }
        }
    }

    /**
     * Creates or updates a node at the specified path.
     *
     * This circumvents sabredav's internal server apis, so all events and
     * access control is skipped.
     *
     * @param string $path
     * @param string $data
     * @return void
     */
    public function putPath($path, $data)
    {
        list($parent, $base) = \Sabre\HTTP\UrlUtil::splitPath($path);
        $parentNode = $this->server->tree->getNodeForPath($parent);
        $parentNode->createFile($base, $data);
    }
}
