<?php

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

class TeamBasedACLConfigurator
{
    const CONFIG_KEY = 'team_based_acl';

    /**
     * @var array List of TBA field constants.
     */
    protected $fieldOptions = array(
        'ACL_READ_SELECTED_TEAMS_WRITE' => 65,
        'ACL_SELECTED_TEAMS_READ_OWNER_WRITE' => 70,
        'ACL_SELECTED_TEAMS_READ_WRITE' => 75,
    );

    /**
     * @var array List of TBA module constants.
     */
    protected $moduleOptions = array(
        'ACL_ALLOW_SELECTED_TEAMS' => 70,
    );

    /**
     * @var string Fields fallback key.
     */
    protected $fieldFallbackOption = 'ACL_OWNER_READ_WRITE';
    /**
     * @var string Modules fallback key.
     */
    protected $moduleFallbackOption = 'ACL_ALLOW_OWNER';

    /**
     * Get TBA field options.
     * @return array
     */
    public function getFieldOptions()
    {
        return $this->fieldOptions;
    }

    /**
     * Get TBA module options.
     * @return array
     */
    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    /**
     * Get field fallback option.
     * @return string
     */
    public function getFieldFallbackOption()
    {
        return $this->fieldFallbackOption;
    }

    /**
     * Get module fallback option.
     * @return string
     */
    public function getModuleFallbackOption()
    {
        return $this->moduleFallbackOption;
    }

    /**
     * @param $module
     * @param boolean $enable
     */
    public function setForModule($module, $enable)
    {
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting($module, self::CONFIG_KEY, (int)$enable, 'base');
        if (!$enable) {
            $this->fallbackTBA($module);
        }
        $this->clearVardefs($module);
    }

    /**
     * Is Team Based ACL enabled for module, if not set - uses global value.
     * @param $module
     * @return bool
     */
    public function isEnabledForModule($module)
    {
        if (!$this->isEnabledGlobally()) {
            return false;
        }
        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule($module);

        return isset($config[self::CONFIG_KEY]) ? (bool)$config[self::CONFIG_KEY] : true;
    }

    /**
     * Set global state of the Team Based ACL.
     * @param boolean $enable
     */
    public function setGlobal($enable)
    {
        $cfg = new Configurator();
        $cfg->config[self::CONFIG_KEY] = (bool)$enable;
        $cfg->handleOverride();
        if (!$enable) {
            $this->fallbackTBA();
        }
        SugarConfig::getInstance()->clearCache();
        $this->clearVardefs();
    }

    /**
     * Global state of the Team Based ACL.
     * @return boolean
     */
    public function isEnabledGlobally()
    {
        return SugarConfig::getInstance()->get(self::CONFIG_KEY, false);
    }

    /**
     * Update modules vardefs to apply the Team Based visibility.
     * @param string $module Module name.
     */
    protected function clearVardefs($module = null)
    {
        if ($module) {
            $bean = BeanFactory::getBean($module);
            VardefManager::clearVardef($bean->module_dir, $bean->object_name);
        } else {
            VardefManager::clearVardef();
        }
    }

    /**
     * Fallback all roles options in case of TBA disabling.
     * Don't pass module to affect all modules in all roles.
     * @param string $module Module name.
     */
    protected function fallbackTBA($module = null)
    {
        $aclRole = new ACLRole();
        $aclField = new ACLField();
        $fieldOptions = $this->getFieldOptions();

        $allRoles = $aclRole->getAllRoles();
        foreach ($allRoles as $role) {
            $actions = $aclRole->getRoleActions($role->id);
            $fields = $aclField->getACLFieldsByRole($role->id);

            foreach ($actions as $aclKey => $aclModule) {
                if ($module && $aclKey != $module) {
                    continue;
                }
                if (empty($aclModule['module'])) {
                    continue;
                }
                foreach ($aclModule['module'] as $action) {
                    if (in_array($action['aclaccess'], $this->getModuleOptions())) {
                        $aclRole->setAction(
                            $role->id,
                            $action['id'],
                            constant($this->getModuleFallbackOption())
                        );
                    }
                }
            }
            if ($fields) {
                $tbaRecords = array_filter($fields, function ($val) use ($module, $fieldOptions) {
                    if ($module && $val['category'] != $module) {
                        return false;
                    }
                    return in_array($val['aclaccess'], $fieldOptions);
                });
                foreach ($tbaRecords as $fieldToReset) {
                    $aclField->setAccessControl(
                        $fieldToReset['category'],
                        $role->id,
                        $fieldToReset['name'],
                        constant($this->getFieldFallbackOption())
                    );
                }
            }
        }
    }
}
