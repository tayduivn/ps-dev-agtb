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

namespace Sugarcrm\Sugarcrm\SugarConnect\LogicHooks;

use Sugarcrm\Sugarcrm\SugarConnect\Publisher;
use Sugarcrm\Sugarcrm\SugarConnect\Bean\SugarBean;
use Sugarcrm\Sugarcrm\SugarConnect\Configuration\Configuration;
use Sugarcrm\Sugarcrm\SugarConnect\Configuration\Repository;

final class Handler implements Publisher
{
    /**
     * The SugarConnect configuration.
     *
     * @var Repository
     */
    private $config;

    /**
     * This is the entry point for logic hooks.
     *
     * @param ?Repository $config The SugarConnect configuration. The default is
     *                            an instance of {@link Configuration}.
     */
    public function __construct(?Repository $config = null)
    {
        $this->config = $config ?? new Configuration();
    }

    /**
     * Triggered by logic hooks to begin publishing bean changes to the Sugar
     * Connect webhook.
     *
     * @param \SugarBean $bean  The bean that was saved.
     * @param string     $event The event type.
     * @param array      $args  Additional arguments.
     *
     * @return void
     */
    public function publish(\SugarBean $bean, string $event, array $args) : void
    {
        // Stop before you start.
        if (!$this->config->isEnabled()) {
            return;
        }

        // Don't let any exceptions bubble up.
        try {
            SugarBean::getInstance($bean)->publish($bean, $event, $args);
        } catch (\Exception $e) {
            $log = \LoggerManager::getLogger();
            $log->fatal("sugar connect: logic hooks: {$e->getMessage()}");
        }
    }
}
