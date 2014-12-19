<?php

require_once 'modules/ModuleBuilder/parsers/ModuleBuilderParser.php';

/**
 * Parser for role based dropdowns
 * Stores data in (custom/)include/language/roles/<role_id> directory
 *
 */
class ParserRoleDropDown extends ModuleBuilderParser
{
    protected $path = 'include/language/roles';
    protected $varName = 'roledropdown';

    /**
     * Returns array of options by given role name and dropdown name
     *
     * @param $role role name
     * @param $dropdown dropdown name
     * @return array
     */
    public function getOne($role, $dropdown)
    {
        $filePath = SugarAutoLoader::existingCustomOne($this->getFilePath($role, $dropdown));
        if (!file_exists($filePath)) {
            return array();
        }
        require $filePath;
        if (empty(${$this->varName}[$dropdown])) {
            $GLOBALS['log']->error("ParserRoleDropDown :: Cannot find \$$this->varName[$dropdown] in $filePath");
            return array();
        }
        return ${$this->varName}[$dropdown];
    }

    /**
     * Checks if there is role specific metadata for the given dropdown field
     *
     * @param string $name Field name
     * @param string $role Role ID
     *
     * @return boolean
     */
    public function hasMetadata($name, $role)
    {
        $filePath = SugarAutoLoader::existingCustomOne($this->getFilePath($role, $name));
        return file_exists($filePath);
    }

    /**
     * Returns an array of all dropdown options for all roles
     *
     * @return array
     */
    public function getAll()
    {
        return $this->getDropDownFiltersFromFiles($this->getAllFiles());
    }

    /**
     * @return array list of all Role Based language files.
     */
    public function getAllFiles() {
        return array_merge(
            glob($this->path . '/*/*.php'),
            glob('custom/' . $this->path . '/*/*.php'),
            glob('custom/application/Ext/Language/*/*/roledropdown.ext.php')
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
        ${$this->varName} = array();
        foreach ($files as $file) {
            if (is_array($file) && isset($file['path'])) {
                $file = $file['path'];
            }
            if (is_string($file) && SugarAutoLoader::fileExists($file)) {
                require $file;
            }

        }

        return ${$this->varName};
    }

    /**
     * Returns a file path to the file that stores options for a given role and a dropdown name
     *
     * @param $role
     * @param $name
     * @return string
     */
    protected function getFilePath($role, $name)
    {
        return $this->getFileDir($role) . "/$name.php";
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
     * @param $role
     * @param $name
     * @param $data
     * @return boolean
     * @throws Exception
     */
    public function handleSave($role, $name, $data)
    {
        $dir = 'custom/' . $this->getFileDir($role);
        if (!SugarAutoLoader::ensureDir($dir)) {
            $GLOBALS['log']->error("ParserRoleDropDown :: Cannot create directory $dir");
            return false;
        }
        $result = write_array_to_file(
            "{$this->varName}['{$name}']",
            $this->convertFormData($data),
            'custom/' . $this->getFilePath($role, $name)
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
