<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
/**
 * Recreate hierarchy search stored procedures
 */
class SugarUpgradeRemoveFiles extends UpgradeScript
{
    public $order = 8501;
    public $type = self::UPGRADE_CORE;

    public function run()
    {

        // we only need to remove these files if the from_version is less than 7.0.0
        if (version_compare($this->from_version, '7.0.0', '<')
            && version_compare($this->from_version, '7.0.0', '<')
        ) {
            // files to delete
            $files = array(
                'controller.php',
                'Delete.php',
                'field_arrays.php',
                'index.php',
                'ListView.html',
                'ListView.php',
                'Menu.php',
                'Popup_picker.html',
                'Popup_picker.php',
                'index.php',
                'Save.php',
                'TreeData.php',
                'index.php',
                'views/view.detail.php',
                'views/view.edit.php',
                'metadata/editviewdefs.php',
            );

            $module_path = "modules/ProductCategories/";
            foreach ($files as $file) {
                $this->state['files_to_delete'][] = $module_path . $file;
            }
        }
    }
}
