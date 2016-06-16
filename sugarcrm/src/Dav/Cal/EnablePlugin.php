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

use Sabre\DAV\Exception\ServiceUnavailable;
use Sabre\DAV\Server;
use Sabre\CalDAV\Plugin;

/**
 * Class EnablePlugin
 * @package Sugarcrm\Sugarcrm\Dav\Cal
 */
class EnablePlugin extends Plugin
{
    /**
     * @var \Configurator
     */
    protected $configurator;

    /**
     * @var Server
     */
    protected $server;

    /**
     * Plugin constructor.
     *
     * @param \Configurator $configurator
     */
    public function __construct(\Configurator $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * {@inheritdoc}
     */
    public function getPluginName()
    {
        return 'check-is-enabled';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(Server $server)
    {
        $this->server = $server;
        $this->server->once('beforeMethod', array($this, 'checkIsEnabled'));
    }

    /**
     * Checking is calendar enabled.
     *
     * @throws ServiceUnavailable
     */
    public function checkIsEnabled()
    {
        if (empty($this->configurator->config['caldav_enable_sync'])) {
            throw new ServiceUnavailable('Service unavailable');
        }
    }
}
