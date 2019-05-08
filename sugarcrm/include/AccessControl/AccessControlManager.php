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

namespace Sugarcrm\Sugarcrm\AccessControl;

// This section of code is a portion of the code referred
// to as Critical Control Software under the End User
// License Agreement.  Neither the Company nor the Users
// may modify any portion of the Critical Control Software.

/**
 * Class AccessControlManager
 *
 * check user's access permission.
 *
 * This is a singleton class
 *
 * @package Sugarcrm\Sugarcrm\AccessControl
 */
class AccessControlManager
{
    const MODULES_KEY = 'MODULES';
    const DASHLETS_KEY = 'DASHLETS';
    const RECORDS_KEY = 'RECORDS';
    const FIELDS_KEY = 'FIELDS';

    /**
     * flag to allow admin user to override access control
     * @var bool
     */
    protected $allowAdminOverride = false;

    /**
     * @var array
     */
    protected $voters = [];

    /**
     * instance
     * @var AccessControlManager
     */
    protected static $instance;

    /**
     * module control list
     * @var array
     */
    protected $moduleAclList = [];

    /**
     * access controlled list
     * @var array
     */
    protected $accessControlledList = [];

    /**
     * private ctor
     * AccessControlManager constructor
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * init object
     */
    protected function init()
    {
        $this->registerVoters();
    }

    /**
     * Singleton impl
     * @return AccessControlManager
     */
    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new AccessControlManager();
        }

        return self::$instance;
    }

    /**
     * registers available voters
     */
    protected function registerVoters()
    {
        // MODULES_KEY and DASHLETS_KEY are shared same voter
        $this->registerVoter(self::MODULES_KEY, SugarVoter::class);
        $this->registerVoter(self::RECORDS_KEY, SugarRecordVoter::class);
        $this->registerVoter(self::FIELDS_KEY, SugarFieldVoter::class);
    }

    /**
     * Register a new Voter on the stack
     * @param string $identifier Voter identifier
     * @param string $class Classname
     */
    protected function registerVoter(string $identifier, string $class)
    {
        $this->voters[$identifier] = new $class();
    }

    /**
     * Return list of registered Voters
     * @return array
     */
    protected function getRegisteredVoter(string $key)
    {
        if ($key != self::DASHLETS_KEY && !isset($this->voters[$key])) {
            throw new \Exception("wrong section key is provided" . $key);
        }
        switch ($key) {
            case self::MODULES_KEY:
            case self::DASHLETS_KEY:
                return $this->voters[self::MODULES_KEY];
            default:
                return $this->voters[$key];
        }
    }
    
    /**
     *
     * check if allowed to access protected resource
     *
     * @param mixed  $subject The subject to secure, could be subject identifier, such modules, fields
     * @param array $attributes list of attributes, such as edit, view, etc
     *
     */
    protected function allowAccess(string $key, string $subject, ?string $value = null) : bool
    {
        // bypassing access check during installation
        if (isset($GLOBALS['installing']) && $GLOBALS['installing'] === true) {
            return true;
        }

        global $current_user;
        // admin override
        if ($this->allowAdminOverride && !empty($current_user) && is_admin($current_user)) {
            return true;
        }

        return $this->getRegisteredVoter($key)->vote($key, $subject, $value);
    }


    /**
     * check allow module access
     *
     * @param string $module module name
     *
     * @return bool
     */
    public function allowModuleAccess(?string $module) : bool
    {
        if (empty($module)) {
            return true;
        }

        if (!$this->isAccessControlled(self::MODULES_KEY, $module)) {
            $this->moduleAclList[$module] = true;
            return true;
        }

        if (isset($this->moduleAclList[$module])) {
            return $this->moduleAclList[$module];
        }

        $allowAccess = $this->allowAccess(self::MODULES_KEY, $module);
        $this->moduleAclList[$module] = $allowAccess;
        return $allowAccess;
    }

    /**
     * check allow dashlet access
     *
     * @param string $label dashlet name
     * @return bool
     */
    public function allowDashletAccess(?string $label) : bool
    {
        if (empty($label)) {
            return true;
        }
        return $this->allowAccess(self::DASHLETS_KEY, $label);
    }

    /**
     * check allow record access
     * @param null|string $module module name
     * @param null|string $id id for the object
     * @return bool
     */
    public function allowRecordAccess(?string $module, ?string $id) : bool
    {
        if (empty($module) || empty($id)) {
            return true;
        }

        if (!$this->isAccessControlled(self::RECORDS_KEY, $module)) {
            return true;
        }

        return $this->allowAccess(self::RECORDS_KEY, $module, $id);
    }

    /**
     * check allow module field access
     *
     * @param string $module module name
     * @param string $field field name
     * @param array $attributes
     * @return bool
     */
    public function allowFieldAccess(?string $module, ?string $field)
    {
        if (empty($module) || empty($field)) {
            return true;
        }

        if (!$this->isAccessControlled(self::FIELDS_KEY, $module)) {
            return true;
        }

        return $this->allowAccess(self::FIELDS_KEY, $module, $field);
    }

    /**
     * allow admin override access control
     * @param bool $override
     */
    public function allowAdminOverride(bool $override)
    {
        $this->allowAdminOverride = $override;
    }

    /**
     * check if the module is subjected to access control
     *
     * @param string $key
     * @param string $module
     * @return bool
     */
    protected function isAccessControlled(string $key, string $module) : bool
    {
        if (!isset($this->accessControlledList[$key])) {
            $this->accessControlledList[$key] = $this->getAccessControlledList($key);
        }
        return isset($this->accessControlledList[$key][$module]);
    }

    /**
     * get access controlled list
     * @param $key
     * @return array
     */
    protected function getAccessControlledList(string $key) : array
    {
        return AccessConfigurator::instance()->getAccessControlledList($key);
    }
}
//END REQUIRED CODE DO NOT MODIFY
