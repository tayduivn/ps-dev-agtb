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

require_once 'modules/ACLActions/actiondefs.php';

class TeamBasedACLSetupTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TeamBasedACLConfigurator
     */
    protected $tbaConfig;

    /**
     * @var boolean
     */
    protected $globalTBA;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var ACLRole
     */
    protected $role;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, true));
        $this->tbaConfig = $this->getMock('TeamBasedACLConfigurator', array('clearVardefs'));
        $this->globalTBA = $GLOBALS['sugar_config'][TeamBasedACLConfigurator::CONFIG_KEY]['enabled'];

        $this->tbaConfig->setGlobal(true);
        $this->tbaConfig->setForModule($this->module, true);
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->role = new ACLRole();
        $this->role->save();
    }

    public function tearDown()
    {
        $this->role->mark_deleted($this->role->id);
        $this->tbaConfig->setGlobal($this->globalTBA);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * Disabled globally should affect modules as well.
     */
    public function testGlobalConfig()
    {
        $this->tbaConfig->setGlobal(false);

        $this->assertFalse($this->tbaConfig->isEnabledGlobally());
        $this->assertFalse($GLOBALS['sugar_config'][TeamBasedACLConfigurator::CONFIG_KEY]['enabled']);
        $this->assertFalse($this->tbaConfig->isEnabledForModule($this->module));

        $this->tbaConfig->setGlobal(true);
        $this->assertTrue($this->tbaConfig->isEnabledGlobally());
        $this->assertTrue($GLOBALS['sugar_config'][TeamBasedACLConfigurator::CONFIG_KEY]['enabled']);
    }

    /**
     * Test module level.
     */
    public function testModuleConfig()
    {
        $this->tbaConfig->setGlobal(true);

        $this->tbaConfig->setForModule($this->module, false);
        $this->assertFalse($this->tbaConfig->isEnabledForModule($this->module));

        $this->tbaConfig->setForModule($this->module, true);
        $this->assertTrue($this->tbaConfig->isEnabledForModule($this->module));

        $this->tbaConfig->setGlobal(false);
        $this->assertFalse($this->tbaConfig->isEnabledForModule($this->module));
    }

    /**
     * Test cases:
     * Fallback - should replace TBA options.
     * Restore - should restore the previous TBA options.
     * Restore changed ACL - should not restore changed ACL.
     */
    public function testFallbackAndRestore()
    {
        $action = 'view';
        $field = 'name';
        $aclField = new ACLField();
        $roleActions = $this->role->getRoleActions($this->role->id);
        $fallbackField = $this->tbaConfig->getFieldFallbackOption();
        $fallbackModule = $this->tbaConfig->getModuleFallbackOption();

        // Set ACL for Module.
        $actionId = $roleActions[$this->module]['module'][$action]['id'];
        $this->role->setAction($this->role->id, $actionId, ACL_ALLOW_SELECTED_TEAMS);

        // Set ACL for Field.
        $aclField->setAccessControl($this->module, $this->role->id, $field, ACL_SELECTED_TEAMS_READ_WRITE);

        // Fallback.
        $this->tbaConfig->setForModule($this->module, false);

        $actualActions = $this->role->getRoleActions($this->role->id);
        $this->assertEquals(constant($fallbackModule), $actualActions[$this->module]['module'][$action]['aclaccess']);

        $actualAclFields = $aclField->getACLFieldsByRole($this->role->id);
        $fieldKeys = array_keys($actualAclFields);
        $this->assertEquals(constant($fallbackField), $actualAclFields[$fieldKeys[0]]['aclaccess']);

        // Restore.
        $this->tbaConfig->setForModule($this->module, true);

        $actualActions = $this->role->getRoleActions($this->role->id);
        $this->assertEquals(ACL_ALLOW_SELECTED_TEAMS, $actualActions[$this->module]['module'][$action]['aclaccess']);

        $actualAclFields = $aclField->getACLFieldsByRole($this->role->id);
        $fieldKeys = array_keys($actualAclFields);
        $this->assertEquals(ACL_SELECTED_TEAMS_READ_WRITE, $actualAclFields[$fieldKeys[0]]['aclaccess']);

        // Restore changed ACL
        $this->tbaConfig->setForModule($this->module, false);

        $actionId = $roleActions[$this->module]['module'][$action]['id'];
        $this->role->setAction($this->role->id, $actionId, ACL_ALLOW_DEFAULT);
        $aclField->setAccessControl($this->module, $this->role->id, $field, ACL_ALLOW_DEFAULT);

        $this->tbaConfig->setForModule($this->module, true);

        $actualActions = $this->role->getRoleActions($this->role->id);
        $this->assertEquals(ACL_ALLOW_DEFAULT, $actualActions[$this->module]['module'][$action]['aclaccess']);

        $actualAclFields = $aclField->getACLFieldsByRole($this->role->id);
        $fieldKeys = array_keys($actualAclFields);
        $this->assertEquals(ACL_ALLOW_DEFAULT, $actualAclFields[$fieldKeys[0]]['aclaccess']);
    }
}
