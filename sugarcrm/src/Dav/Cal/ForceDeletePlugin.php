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

namespace Sugarcrm\Sugarcrm\Dav\Cal;

use Sabre\DAV\Server;
use Sabre\CalDAV\Plugin;

/**
 * Plug-in forcing the removal of the object. It makes ignore the if-match header.
 *
 * Class ForceDeletePlugin
 * @package Sugarcrm\Sugarcrm\Dav\Cal
 */
class ForceDeletePlugin extends Plugin
{
    /** @var Server */
    protected $server;

    /**
     * {@inheritdoc}
     */
    public function getPluginName()
    {
        return 'force-delete';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(Server $server)
    {
        $this->server = $server;
        $this->server->once('beforeMethod', array($this, 'forceDelete'));
    }

    /**
     * This function  makes ignore the if-match header on http method delete.
     */
    public function forceDelete()
    {
        /** @var \Sabre\HTTP\Request $httpRequest */
        $httpRequest = $this->server->httpRequest;
        if (strtoupper($httpRequest->getMethod()) == 'DELETE') {
            $httpRequest->setHeader('If-Match', '*');
        }
    }
}
