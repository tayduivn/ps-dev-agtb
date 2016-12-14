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

namespace Sugarcrm\Sugarcrm\Console\Command\Thorn;

use Sugarcrm\Sugarcrm\Console\CommandRegistry\Mode\InstanceModeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MetaDataManager;
use VardefManager;

/**
 * Thorn's metadata builder.
 */
class BuildMetadataCommand extends Command implements InstanceModeInterface
{

    /**
     * {inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('thorn:build-metadata')
            ->setDescription(
                "Build Thorn's metadata." .
                'As this will generate a lot of output in JSON format, it is ' .
                'recommended to redirect the output to a file for later processing.'
            )
            ->addOption(
                'modules',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Comma separated list of modules to generate metadata from. If none supplied ' .
                'metadata is generated for all available modules.'
            );
    }

    /**
     * {inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modules = $input->getOption('modules');

        if ($modules) {
            $modules = explode(',', $modules);
        } else {
            $modules = $this->getModuleList();
        }

        $result = array();
        foreach ($modules as $module) {
            $fields = $this->getFields($module);
            if (!$fields) {
                continue;
            }

            $requiredFields = $this->filterByRequiredFields($fields);

            $result[$module] = array(
                'fields' => $requiredFields,
            );
        }

        $json = json_encode($result, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);

        $output->writeln($json);
    }

    /**
     * Returns modules list.
     * @return array
     */
    protected function getModuleList()
    {
        return MetadataManager::getManager()->getModuleList();
    }

    /**
     * Returns list of fields for supplied module.
     * @param string $module
     * @return array|null
     */
    protected function getFields($module)
    {
        return VardefManager::getFieldDefs($module);
    }

    /**
     * Returns a list of fields considered to be required based on supplied
     * fields.
     *
     * Fields are considered required when they're set as required, have no
     * source, are not set as readonly and their types do not match 'id'.
     *
     * @param array $fields
     * @return array
     */
    public function filterByRequiredFields(array $fields)
    {
        return array_filter($fields, function ($field) {
            return !empty($field['required'])
            && empty($field['source'])
            && empty($field['readonly'])
            && (empty($field['type']) || $field['type'] !== 'id');
        });
    }
}
