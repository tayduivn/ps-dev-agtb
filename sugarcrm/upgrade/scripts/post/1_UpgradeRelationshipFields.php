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

/**
 * Upgrade relationship fields from list to map format
 */
class SugarUpgradeUpgradeRelationshipFields extends UpgradeScript
{
    // the script should run before 1_ClearVarDefs in order to make sure existing relationship cache and the extensions
    // which the new cache can built from are up to date
    public $order = 1090;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        $files = $this->getFiles();
        foreach ($files as $file) {
            $this->processFile($file);
        }

        $this->convertRelationshipCache();
    }

    protected function getFiles()
    {
        return array_merge(
            // relationships created in Studio
            glob('custom/metadata/*MetaData.php'),
            // relationships defined by installed extensions in Module Builder
            glob('custom/Extension/modules/relationships/relationships/*MetaData.php')
        );
    }

    protected function loadDefinition($name, $file)
    {
        $dictionary = array();
        include $file;
        return $dictionary[$name];
    }

    protected function processFile($file)
    {
        $name = basename($file, 'MetaData.php');
        $definition = $this->loadDefinition($name, $file);
        if (empty($definition['fields'])) {
            $this->log('No field definitions for ' . $name);
            return;
        }

        $converted = $this->convertFields($name, $definition['fields']);

        if (array_keys($definition['fields']) === array_keys($converted)) {
            $this->log('Field definitions for relationship ' . $name . ' are in correct format');
            return;
        }

        $definition['fields'] = $converted;
        $this->log('Saving converted field definitions for relationship ' . $name);
        write_array_to_file('dictionary[\'' . $name . '\']', $definition, $file);
    }

    protected function convertRelationshipCache()
    {
        $factory = SugarRelationshipFactory::getInstance();
        $getCacheFile = new ReflectionMethod($factory, 'getCacheFile');
        $getCacheFile->setAccessible(true);
        $file = $getCacheFile->invoke($factory);

        if (!file_exists($file)) {
            $this->log('Relationship cache file does not exist');
            return;
        }

        $saveNeeded = false;
        $relationships = array();
        require $file;

        foreach ($relationships as $name => $definition) {
            if (empty($definition['fields'])) {
                continue;
            }

            $converted = $this->convertFields($name, $definition['fields']);

            if (array_keys($definition['fields']) !== array_keys($converted)) {
                $relationships[$name]['fields'] = $converted;
                $this->log('Converted field definitions for relationship ' . $name . ' in relationship cache');
                $saveNeeded = true;
            }
        }

        if (!$saveNeeded) {
            $this->log('No definitions from relationship cache have been converted');
            return;
        }

        $this->log('Saving converted relationship cache');
        write_array_to_file('relationships', $relationships, $file);

        $loadRelationships = new ReflectionMethod($factory, 'loadRelationships');
        $loadRelationships->setAccessible(true);
        $this->log('Reloading relationship cache');
        $loadRelationships->invoke($factory);
    }

    protected function convertFields($name, array $fields)
    {
        $converted = array();

        foreach ($fields as $key => $value) {
            if (!isset($value['name'])) {
                $this->log('No field name in relationship ' . $name . ' definition for key ' . $key);
                $newKey = $key;
            } else {
                $newKey = $value['name'];
            }
            $converted[$newKey] = $value;
        }

        return $converted;
    }
}
