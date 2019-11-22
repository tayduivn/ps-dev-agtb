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

namespace Sugarcrm\Sugarcrm\Console\Command\Api;

use Sugarcrm\Sugarcrm\Console\CommandRegistry\Mode\InstanceModeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputArgument;

/**
 *
 * Idm Mode Switcher
 *
 */
class IdmModeManageCommand extends Command implements InstanceModeInterface
{
    const IDM_CONFIG_FILE = 'config_idm.php';
    use ApiEndpointTrait;

    /**
     * {inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('idm-mode:manage')
            ->setDescription('enable, disable or migrate IDM mode')
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                'enable or disable',
                null
            )
            ->addOption(
                'file',
                '-f',
                InputOption::VALUE_NONE,
                'The file contains the idm fields if action is enable, default file is config_idm.php.'
            )
        ;
    }

    /**
     * {inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $args = [];
        $args['idmMode'] = [];
        $action = $input->getArgument('action');

        if (!in_array($action, array('enable', 'disable', 'migrate'), true)) {
            throw new \Exception("Please specify a proper action: 'enable', 'disable' or 'migrate'");
        }

        $enbled = ($action === 'enable');

        if ($enbled) {
            $file = $input->getOption('file');
            if (empty($file)) {
                $file = self::IDM_CONFIG_FILE;
            }
            if (file_exists($file)) {
                global $sugar_idm_config;
                require $file;
                $args['idmMode'] = $sugar_idm_config;
                $args['idmMode']['enabled'] = $enbled;
            } else {
                throw new \Exception("Cannot find file '{$file}'");
            }
        }

        if ($action === 'migrate') {
            // migrate from config to db
            $output->writeln('migrate IDM storage from config file to database ...');
            $idmEnabled = (bool)\SugarConfig::getInstance()->get('idm_mode.enabled', false);
            if (!$idmEnabled) {
                $output->writeln('there is no data in config, exit.');
                return;
            }

            $args['idmMode'] = \SugarConfig::getInstance()->get('idm_mode');
        }

        if (!empty($args['idmMode'])) {
            $oldSetting = null;
            if ($GLOBALS['sugar_config']['idmMigration']) {
                $oldSetting = $GLOBALS['sugar_config']['idmMigration'];
            }
            $GLOBALS['sugar_config']['idmMigration'] = true;
            $output->writeln('enable IDM mode, it may take while to refresh cache ...');
            $this->initApi($this->getApi())->callApi('switchOnIdmMode', $args);
            if ($oldSetting !== null) {
                $GLOBALS['sugar_config']['idmMigration'] = $oldSetting;
            } else {
                unset($GLOBALS['sugar_config']['idmMigration']);
            }
        } elseif ($action === 'disable') {
            $output->writeln('enable IDM mode, it may take while to refresh cache ...');
            $this->initApi($this->getApi())->callApi('switchOffIdmMode', $args);
        }
        $output->writeln('Done!');
    }

    /**
     * @return \AuthSettingsApi
     */
    protected function getApi()
    {
        return new \AuthSettingsApi();
    }
}
