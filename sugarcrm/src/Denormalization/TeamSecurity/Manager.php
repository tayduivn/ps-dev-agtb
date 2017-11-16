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

namespace Sugarcrm\Sugarcrm\Denormalization\TeamSecurity;

use DBManagerFactory;
use Psr\Log\LoggerInterface;
use SugarConfig;
use Sugarcrm\Sugarcrm\Dbal\Connection;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Command\RebuildIfNeeded;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Builder\StateBasedBuilder;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Proxy;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State\Storage\AdminSettingsStorage;
use Sugarcrm\Sugarcrm\Logger\Factory as LoggerFactory;

/**
 * Denormalization Manager
 */
class Manager
{
    /**
     * $sugar_config to determine if use of denormalized table is enabled
     * @var string
     */
    const CONFIG_KEY = "perfProfile.TeamSecurity";

    /**
     * @var self
     */
    private static $instance;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var Listener
     */
    private $listener;

    /**
     * Constructor
     *
     * @param Connection $conn
     * @param LoggerInterface $logger
     * @param SugarConfig $config
     */
    public function __construct(Connection $conn, LoggerInterface $logger, SugarConfig $config)
    {
        $this->conn = $conn;
        $this->logger = $logger;

        $this->state = new State(
            $config->get(self::CONFIG_KEY . '.inline_update'),
            $this->getIsEnabledUseDenormOption($config),
            new AdminSettingsStorage(),
            $logger
        );

        $builder = new StateBasedBuilder(
            $conn,
            $this->state
        );

        $this->listener = new Proxy($builder);

        $this->state->attach($this->listener);
    }

    /**
     * Check if use_denorm is enabled for any module and returns the value.
     *
     * @return boolean
     */
    private function getIsEnabledUseDenormOption(SugarConfig $config)
    {
        $moduleConfigs = $config->get(self::CONFIG_KEY, array());

        foreach ($moduleConfigs as $value) {
            if (!empty($value['use_denorm'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Listener
     */
    public function getListener()
    {
        return $this->listener;
    }

    /**
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return RebuildIfNeeded
     */
    public function getRebuildCommand()
    {
        return new RebuildIfNeeded($this->state, $this->conn, $this->logger);
    }

    /**
     * Get DenormManager instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self(
                DBManagerFactory::getConnection(),
                LoggerFactory::getLogger('denorm'),
                SugarConfig::getInstance()
            );
        }

        return self::$instance;
    }

    /**
     * Welcome to the world of singletones!
     */
    public static function resetInstance()
    {
        self::$instance = null;
    }
}
