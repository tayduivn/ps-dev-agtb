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

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;

// This section of code is a portion of the code referred
// to as Critical Control Software under the End User
// License Agreement.  Neither the Company nor the Users
// may modify any portion of the Critical Control Software.

/**
 * Class AccessControlManager
 *
 * using Symfony Authorization Framework to check user's access permission.
 *
 * This is a singleton class
 *
 * @link https://symfony.com/doc/master/components/security/authorization.html
 * @package Sugarcrm\Sugarcrm\AccessControl
 */
class AccessControlManager
{
    const MODULES_KEY = 'MODULES';
    const DASHLETS_KEY = 'DASHLETS';
    const RECORDS_KEY = 'RECORDS';
    const FIELDS_KEY = 'FIELDS';

    /**
     * @var AccessDecisionManager
     */
    protected $accessDecisionMgr;

    /**
     * flag to allow admin user to override access control
     * @var bool
     */
    protected $allowAdminOverride = false;

    /**
     * @var array VoterInterface
     */
    protected $voters = [];

    /**
     * instance
     * @var AccessControlManager
     */
    protected static $instance;

    /**
     * @var string strategy
     */
    protected $strategy = 'affirmative';

    // whether or not to grant access when all voters abstain
    protected $allowIfAllAbstainDecisions = true;

    // whether or not to grant access when there is no majority (applies only to the "consensus" strategy)
    protected $allowIfEqualGrantedDeniedDecisions = true;

    /**
     * private ctor
     * AccessControlManager constructor
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * init objects
     */
    protected function init()
    {
        $this->registerVoters();
        $voters = $this->getRegisteredVoters();
        $this->accessDecisionMgr = new AccessDecisionManager(
            $voters,
            $this->strategy,
            $this->allowIfAllAbstainDecisions,
            $this->allowIfEqualGrantedDeniedDecisions
        );
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
        $this->registerVoter('SugarVoter', SugarVoter::class);
        $this->registerVoter('SecureRecordVoter', SugarRecordVoter::class);
        $this->registerVoter('SugarFieldVoter', SugarFieldVoter::class);
        $this->registerVoter('SecureObjectVoter', SecureObjectVoter::class);
    }

    /**
     * Register a new Voter on the stack
     * @param string $identifier Voter identifier
     * @param string $class Classname
     */
    protected function registerVoter(string $identifier, string $class)
    {
        $this->voters[$identifier] = $class;
    }

    /**
     * Return list of registered Voters
     * @return array
     */
    protected function getRegisteredVoters()
    {
        $voters = [];
        foreach (array_values($this->voters) as $voterClass) {
            $voters[] = new $voterClass();
        }

        return $voters;
    }
    
    /**
     *
     * check if allowed to access protected resource
     *
     * @param mixed  $subject   The subject to secure, could be subject identifier, such MODULES, REPORTS, or objects
     * @param array $attributes list of attributes, such as edit, view, etc
     *
     */
    protected function allowAccess($subject, array $attributes = ['r'])
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

        $userToken = $this->getUserToken();
        // make sure $attributes is not empty, otherwise, vote will be returning abstain
        if (empty($attributes)) {
            $attributes = ['rw'];
        }
        return $this->accessDecisionMgr->decide($userToken, $attributes, $subject);
    }

    /**
     * check allow module access
     *
     * @param string $module module name
     * @param array $attributes
     * @return bool
     */
    public function allowModuleAccess(?string $module, array $attributes = ['r'])
    {
        if (empty($module)) {
            return true;
        }
        return $this->allowAccess([self::MODULES_KEY => $module], $attributes);
    }

    /**
     * check allow dashlet access
     *
     * @param string $label dashlet name
     * @param array $attributes
     * @return bool
     */
    public function allowDashletAccess(?string $label, array $attributes = ['r'])
    {
        if (empty($label)) {
            return true;
        }
        return $this->allowAccess([self::DASHLETS_KEY => $label], $attributes);
    }

    /**
     * check allow record access
     *
     * @param string $label record name
     * @param array $attributes
     * @return bool
     */
    public function allowRecordAccess(?string $module, ?string $id, array $attributes = ['r'])
    {
        if (empty($module) || empty($id)) {
            return true;
        }
        return $this->allowAccess([self::RECORDS_KEY => [$module => $id]], $attributes);
    }

    /**
     * check allow module field access
     *
     * @param string $module module name
     * @param string $field field name
     * @param array $attributes
     * @return bool
     */
    public function allowFieldAccess(?string $module, ?string $field, array $attributes = ['r'])
    {
        if (empty($module) || empty($field)) {
            return true;
        }
        return $this->allowAccess([self::FIELDS_KEY => [$module => $field]], $attributes);
    }

    /**
     * placeholder for impl of TokenInterface
     * @return UsernamePasswordToken
     */
    protected function getUserToken()
    {
        // we are not using any property of TokenInterface in the Sugar's voters
        return new UsernamePasswordToken('any', 'any', 'any');
    }


    /**
     * allow admin override access control
     * @param bool $override
     */
    public function allowAdminOverride(bool $override)
    {
        $this->allowAdminOverride = $override;
    }
}
