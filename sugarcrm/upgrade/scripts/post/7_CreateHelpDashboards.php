<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

class SugarUpgradeCreateHelpDashboards extends UpgradeScript
{
    public $order = 7500;
    public $type = self::UPGRADE_DB;

    public function run()
    {

        if (version_compare($this->from_version, '7.2', '<')) {
            $sql = "SELECT d.dashboard_module,
                           d.view_name,
                           d.assigned_user_id,
                           (SELECT d1.dashboard_type
                            FROM   dashboards AS d1
                            WHERE  d1.dashboard_module = d.dashboard_module
                               AND d1.view_name = d.view_name
                               AND d1.assigned_user_id = d.assigned_user_id
                               AND d1.dashboard_type = 'help-dashboard') as has_help_dashboard
                    FROM   dashboards AS d
                    GROUP  BY d.assigned_user_id,
                              d.dashboard_module,
                              d.view_name;";

            $results = $this->db->query($sql);


            // get the view defs from the help-dashboard.php file if it exists
            $viewdefs = array();
            if (SugarAutoLoader::load('clients/base/layouts/help-dashboard/help-dashboard.php') &&
                isset($viewdefs['base']['layout']['help-dashboard']['metadata'])) {
                $helpDashboardMeta = json_encode($viewdefs['base']['layout']['help-dashboard']['metadata']);
            } else {
                $helpDashboardMeta = '{"components":[{"rows":[[{"view":{"type":"help-dashlet","label":"LBL_DEFAULT_HELP_DASHLET_TITLE"},"width":12}]],"width":12}]}';
            }

            while ($row = $this->db->fetchByAssoc($results)) {
                if (!empty($row['has_help_dashboard'])) {
                    continue;
                }

                $sqlInsert = "INSERT INTO dashboards (id, name, date_entered, date_modified, modified_user_id, created_by,
                    description, deleted, assigned_user_id, dashboard_module, view_name, metadata, dashboard_type)
                  VALUES
                    ('" . create_guid() . "', 'LBL_DEFAULT_HELP_DASHLET_TITLE',
                        '" . $GLOBALS['timedate']->nowDb() . "', '" . $GLOBALS['timedate']->nowDb() . "',
                        '" . $row['assigned_user_id'] . "', '" . $row['assigned_user_id'] . "', NULL, 0,
                        '" . $row['assigned_user_id'] . "', '" . $row['dashboard_module'] . "', '" . $row['view_name'] . "',
                        '" . $helpDashboardMeta . "', 'help-dashboard');";

                $this->db->query($sqlInsert);

            }
        }
    }
}
