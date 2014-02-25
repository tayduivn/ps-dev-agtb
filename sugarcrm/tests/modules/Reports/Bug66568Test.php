<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once('modules/Reports/templates/templates_reports.php');
require_once('modules/Reports/Report.php');

/**
 * Test all cases if user is allowed to export a report
 *
 * @see hasExportAccess()
 */
class Bug66568Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $args;
    private $reportDef = array(
        'display_columns' => array(),
        'summary_columns' => array(),
        'group_defs' => array(),
        'filters_def' => array(),
        'module' => 'Accounts',
        'assigned_user_id' => '1',
        'report_type' => 'tabular',
        'full_table_list' => array(
            'self' => array(
                'value' => 'Accounts',
                'module' => 'Accounts',
                'label' => 'Accounts',
            ),
        ),
    );

    public function setup()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->role = new ACLRole();
        $this->role->name = 'Bug66568 Test';
        $this->role->save();

        $aclActions = $this->role->getRoleActions($this->role->id);
        $this->role->setAction($this->role->id, $aclActions['Accounts']['module']['export']['id'], ACL_ALLOW_ALL);

        $this->role->load_relationship('users');
        $this->role->users->add($GLOBALS['current_user']);

        $this->args = $args = array(
            'reporter' => new Report(json_encode($this->reportDef))
        );
    }

    public function tearDown()
    {
        global $sugar_config;

        unset($sugar_config['disable_export']);
        unset($sugar_config['admin_export_only']);

        $this->role->mark_deleted($this->role->id);
        SugarTestHelper::tearDown();
    }

    /**
     * Check if proper value is returned when reports export is disabled/enabled
     */
    public function testDisableExportFlag()
    {
        global $sugar_config;

        $sugar_config['disable_export'] = true;
        $this->assertEquals(false, hasExportAccess($this->args), "Exports disabled, shouldn't allow exports");

        $sugar_config['disable_export'] = false;
        $this->assertEquals(true, hasExportAccess($this->args), "Exports enabled, should allow exports");
    }

    /**
     * Check if proper report type is being exported
     */
    public function testReportType()
    {
        $this->args['reporter']->report_def['report_type'] = 'summary';
        $this->assertEquals(false, hasExportAccess($this->args), "Export not tabular, shouldn't allow exports");

        $this->args['reporter']->report_def['report_type'] = 'tabular';
        $this->assertEquals(true, hasExportAccess($this->args), "Exports tabular, should allow exports");
    }

    /**
     * Check if user has proper ACL Roles
     */
    public function testUserRoles()
    {
        $this->assertEquals(true, hasExportAccess($this->args), "User has rights, should allow exports");

        $aclActions = $this->role->getRoleActions($this->role->id);
        $this->role->setAction($this->role->id, $aclActions['Accounts']['module']['export']['id'], ACL_ALLOW_NONE);
        // Clear ACL cache
        $action = BeanFactory::getBean('ACLActions');
        $action->clearACLCache();

        $this->assertEquals(false, hasExportAccess($this->args), "User doesn't have rights, shouldn't allow exports");
    }

    /**
     * Check if only admin export is allowed
     */
    public function testAdminExport()
    {
        global $sugar_config;

        $sugar_config['admin_export_only'] = true;
        $this->assertEquals(false, hasExportAccess($this->args), "User is not admin, shouldn't allow exports");

        SugarTestHelper::setUp('current_user', array(true, 1));
        $this->assertEquals(true, hasExportAccess($this->args), "User is admin, should allow exports");
    }
}
