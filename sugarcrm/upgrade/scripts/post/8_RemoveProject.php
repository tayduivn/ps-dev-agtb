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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

/**
 * Remove Project modules - No longer supported as of 7.0
 */
class SugarUpgradeRemoveProject extends UpgradeScript
{
    public $order = 8501;
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        if (!version_compare($this->from_version, '7.0', '<')) {
            return;
        }

        if ($this->db->tableExists("project") && !$this->db->fetchOne("SELECT id FROM project")) {
            // array of files to be deleted
            $files = array(
                'modules/Project',
                'modules/ProjectResources',
                'modules/ProjectTask',
                'metadata/project_bugsMetaData.php',
                'metadata/project_casesMetaData.php',
                'metadata/project_productsMetaData.php',
                'metadata/project_relationMetaData.php',
                'metadata/project_task_project_tasksMetaData.php',
                'metadata/projects_accountsMetaData.php',
                'metadata/projects_contactsMetaData.php',
                'metadata/projects_opportunitiesMetaData.php',
                'metadata/projects_quotesMetaData.php',
                'modules/Contacts/metadata/subpanels/ForProject.php',
                'modules/Holidays/metadata/subpanels/ForProject.php',
                'modules/Holidays/subpanels/ForProject.php',
                'modules/Opportunities/SubPanelViewProjects.html',
                'modules/Opportunities/SubPanelViewProjects.php',
                'modules/Quotes/SubPanelViewProjects.html',
                'modules/Quotes/SubPanelViewProjects.php',
                'modules/Users/metadata/subpanels/ForProject.php',
            );

            $this->fileToDelete($files);
        }
    }
}
