<?php
// FILE SUGARCRM flav=ent ONLY

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

require_once 'modules/ModuleBuilder/parsers/ModuleBuilderParser.php';

/**
 * Parser for role based dropdowns
 * Stores data in (custom/)include/dropdown_filters/roles/<role_id> directory
 *
 */
class ParserRoleDropDownFilter extends ModuleBuilderParser
{
    protected $path = 'include/dropdown_filters/roles';
    protected $varName = 'role_dropdown_filters';

    /**
     * Checks if there is role specific metadata for the given dropdown field
     *
     * @param string $fieldName Dropdown field name
     * @param string $role Role ID
     *
     * @return boolean
     */
    public function hasMetadata($fieldName, $role)
    {
        $filter = $this->getRawFilter($fieldName, $role);
        return count($filter) > 0;
    }

    /**
     * Returns filter definition for the given dropdown field and role
     *
     * @param string $fieldName Dropdown field name
     * @param string $role Role ID
     * @return array
     */
    public function getOne($fieldName, $role)
    {
        $filter = $this->getRawFilter($fieldName, $role);
        $filter = $this->fixFilter($filter, $fieldName);

        return $filter;
    }

    /**
     * Returns filter definition for the given dropdown field exactly as it's stored in metadata files, or empty array
     * in case if the definition is not found
     *
     * @param string $fieldName Dropdown field name
     * @param string $role Role ID
     * @return array
     */
    protected function getRawFilter($fieldName, $role)
    {
        $paths = array();
        $filterPath = $this->getFilePath($fieldName, $role);
        $filterPath = SugarAutoLoader::existingCustomOne($filterPath);
        if ($filterPath) {
            $paths[] = $filterPath;
        }
        $extPath = 'custom/application/Ext/DropdownFilters/roles/' . $role . '/dropdownfilters.ext.php';
        if (SugarAutoLoader::fileExists($extPath)) {
            $paths[] = $extPath;
        }

        foreach ($paths as $path) {
            $filter = $this->getFilterFromFile($fieldName, $path);
            if ($filter) {
                return $filter;
            }
        }

        return array();
    }

    /**
     * Returns filter metadata for the given dropdown field defined in the file or empty array in case if the
     * field doesn't contain metadata for the given field
     *
     * @param string $fieldName Dropdown field name
     * @param string $file File path
     * @return array
     */
    protected function getFilterFromFile($fieldName, $file)
    {
        $filters = $this->readFiles(array($file));
        if (isset($filters[$fieldName])) {
            return $filters[$fieldName];
        }

        return array();
    }

    /**
     * Fixes filter definition by making sure it contains all options from the default list and doesn't contain
     * options which are not in the default list
     *
     * @param array $filter Raw filter definition
     * @param string $fieldName Dropdown field name
     * @return array
     */
    protected function fixFilter(array $filter, $fieldName)
    {
        global $app_list_strings;

        if (isset($app_list_strings[$fieldName]) && is_array($app_list_strings[$fieldName])) {
            // by default, all options are available
            $defaults = array_fill_keys(array_keys($app_list_strings[$fieldName]), true);
            // remove non-existing options from the filter
            $filter = array_intersect_key($filter, $defaults);
            // add default options to the filter and preserve original key order
            $filter = array_merge($filter, array_diff_key($defaults, $filter));
        }

        return $filter;
    }

    /**
     * Reads medatata files and returns raw filter definition contained in them
     *
     * @param array $files
     * @return array
     */
    protected function readFiles(array $files)
    {
        ${$this->varName} = array();
        foreach ($files as $file) {
            require $file;
        }

        return ${$this->varName};
    }

    /**
     * @return array list of all Role Based language files.
     */
    public function getAllFiles()
    {
        return array_merge(
            glob($this->path . '/*/*.php'),
            glob('custom/' . $this->path . '/*/*.php'),
            glob('custom/application/Ext/DropdownFilters/roles/*/dropdownfilters.ext.php')
        );
    }

    /**
     * Returns editable dropdown filters defined in the given files
     * 
     * @param array $files
     *
     * @return array dropdown filters found in the given files
     */
    public function getDropDownFiltersFromFiles(array $files)
    {
        $filters = $this->readFiles($files);
        foreach ($filters as $fieldName => $filter) {
            $filters[$fieldName] = $this->fixFilter($filter, $fieldName);
        }

        return $filters;
    }

    /**
     * Returns a file path to the file that stores options for a given role and a dropdown name
     *
     * @param $fieldName
     * @param $role
     * @return string
     */
    protected function getFilePath($fieldName, $role)
    {
        return $this->getFileDir($role) . "/$fieldName.php";
    }

    /**
     * Returns a directory for the given role name
     *
     * @param $role
     * @return string
     */
    protected function getFileDir($role)
    {
        return "$this->path/$role";
    }

    /**
     * Saves $data to the $name dropdown for the $role name
     *
     * @param $fieldName
     * @param $role
     * @param $data
     * @return boolean
     */
    public function handleSave($fieldName, $role, $data)
    {
        $dir = 'custom/' . $this->getFileDir($role);
        if (!SugarAutoLoader::ensureDir($dir)) {
            $GLOBALS['log']->error("ParserRoleDropDownFilter :: Cannot create directory $dir");
            return false;
        }
        $result = write_array_to_file(
            "{$this->varName}['{$fieldName}']",
            $this->convertFormData($data),
            'custom/' . $this->getFilePath($fieldName, $role)
        );
        if ($result) {
            MetaDataManager::refreshSectionCache(MetaDataManager::MM_EDITDDFILTERS, array(), array(
                'role' => $role,
            ));
        }
        return $result;
    }

    /**
     * Converts form data to internal representation
     *
     * @param array $data Form data
     * @return array Internal representation
     */
    protected function convertFormData($data)
    {
        $converted = array();
        $blank = translate('LBL_BLANK', 'ModuleBuilder');
        foreach ($data as $key => $item) {
            if ($key === $blank) {
                $key = '';
            }

            $converted[$key] = (bool) $item;
        }

        return $converted;
    }
}
