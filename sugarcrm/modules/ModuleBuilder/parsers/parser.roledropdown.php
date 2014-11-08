<?php

require_once 'modules/ModuleBuilder/parsers/ModuleBuilderParser.php';

/**
 * Parser for role based dropdowns
 * Stores data in (custom/)include/language/roles/<role_id> directory
 *
 */
class ParserRoleDropDown extends ModuleBuilderParser
{
    const BLANK_PLACEHOLDER = '-blank-';

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
        require_once $filePath;
        if (empty(${$this->varName}[$role][$dropdown])) {
            $GLOBALS['log']->error("ParserRoleDropDown :: Cannot find \$$this->varName[$role][$dropdown] in $filePath");
            return array();
        }
        return ${$this->varName}[$role][$dropdown];
    }

    /**
     * Returns an array of all dropdown options for all roles
     *
     * @return array
     */
    public function getAll()
    {
        $files = glob(sugar_root_dir() . '/' . $this->path . '/*/*.php');
        $files += glob(sugar_root_dir() . '/custom/' . $this->path . '/*/*.php');
        ${$this->varName} = array();
        foreach ($files as $file) {
            require $file;
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
            "{$this->varName}['{$role}']['{$name}']",
            $this->parseBlankOptionPlaceholder($data),
            'custom/' . $this->getFilePath($role, $name)
        );
        if ($result) {
            MetaDataManager::refreshSectionCache(MetaDataManager::MM_ROLEDROPDOWNS);
        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     */
    protected function parseBlankOptionPlaceholder($data)
    {
        $keys = array_keys($data);
        $keys = array_map(
            function ($item) {
                return str_replace(self::BLANK_PLACEHOLDER, '', $item);
            },
            $keys
        );
        return array_combine($keys, array_values($data));
    }
}