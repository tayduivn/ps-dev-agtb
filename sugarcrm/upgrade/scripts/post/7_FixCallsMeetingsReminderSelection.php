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
/**
 * Fix separate selection of reminder time for Emails and Popups in Calls & Meetings.
 *
 * From 7.8 there is only one field that is responsible reminder time;
 * selection of the way of notification is set up in Notification Center.
 */
class SugarUpgradeFixCallsMeetingsReminderSelection extends UpgradeScript
{
    public $order = 7406;

    public $type = self::UPGRADE_CUSTOM;

    protected $modules = array('Calls', 'Meetings');

    protected $sidecarViewsToFix = array(
        'record',
        'list',
        'selection-list',
    );

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (!version_compare($this->from_version, '7.8', '<')) {
            // only need to run this upgrading from pre 7.8 versions
            return;
        }

        foreach ($this->modules as $module) {
            foreach ($this->sidecarViewsToFix as $viewName) {
                $this->fixSidecarView($module, $viewName);
            }
            $this->fixPopupdefs($module);
        }

    }

    /**
     * Fix custom viewdefs for sidecar views.
     * @param string $module Module name.
     * @param string $view View name.
     * @param string $platform platform.
     */
    public function fixSidecarView($module, $view, $platform = 'base')
    {
        $fileName = "custom/modules/$module/clients/$platform/views/$view/$view.php";
        $fixesMade = false;
        if (file_exists($fileName)) {
            include $fileName;

            foreach ($viewdefs[$module][$platform]['view'][$view]['panels'] as $panelKey => $panel) {
                foreach ($panel['fields'] as $fieldKey => $field) {
                    if (is_array($field) && isset($field['name']) && $field['name'] === 'reminders') {
                        $canonicalFieldDef = ($this->getCanonicalSidecarFieldDef($module, $view, 'reminders')) ?
                            $this->getCanonicalSidecarFieldDef($module, $view, 'reminders') :
                            $this->getCanonicalSidecarFieldDef($module, $view, 'reminder_time');
                        if ($canonicalFieldDef) {
                            $viewdefs[$module][$platform]['view'][$view]['panels'][$panelKey]['fields'][$fieldKey] =
                                $canonicalFieldDef;
                        } else {
                            unset($viewdefs[$module][$platform]['view'][$view]['panels'][$panelKey]['fields'][$fieldKey]);
                        }
                        $fixesMade = true;
                    } elseif ((is_array($field) && isset($field['name']) && $field['name'] === 'email_reminder_time') ||
                        (is_string($field) && $field === 'email_reminder_time')) {
                        unset($viewdefs[$module][$platform]['view'][$view]['panels'][$panelKey]['fields'][$fieldKey]);
                        $fixesMade = true;
                    } elseif (is_array($field) && isset($field['name']) &&
                        $field['name'] === 'reminder_time' && isset($field['label'])
                        && $field['label'] == 'LBL_POPUP_REMINDER_TIME') {
                        $viewdefs[$module][$platform]['view'][$view]['panels'][$panelKey]['fields'][$fieldKey]['label'] =
                        'LBL_REMINDER_TIME';
                        $fixesMade = true;
                    }
                }
            }
            if ($fixesMade) {
                $this->saveViewDefsToFile('viewdefs', $viewdefs, $fileName);
            }
        }
    }

    /**
     * Fix custom popupdefs.
     * @param string $module Module name.
     */
    public function fixPopupdefs($module)
    {
        $fileName = "custom/modules/$module/metadata/popupdefs.php";
        if (file_exists($fileName)) {
            include $fileName;

            foreach ($popupMeta['listviewdefs'] as $fieldKey => $field) {
                if (is_array($field) && isset($field['name']) && $field['name'] === 'email_reminder_time') {
                    unset($popupMeta['listviewdefs'][$fieldKey]);
                    $this->saveViewDefsToFile('popupMeta', $popupMeta, $fileName);
                    break;
                }
            }
        }
    }

    /**
     * Get field definition from sidecar stock module viewdefs.
     * @param string $module Module name.
     * @param string $view View name.
     * @param string $fieldName Field name we are looking for.
     * @param string $platform platform.
     * @return array|null Field definition if any is found.
     */
    public function getCanonicalSidecarFieldDef($module, $view, $fieldName, $platform = 'base')
    {
        include "modules/$module/clients/$platform/views/$view/$view.php";

        foreach ($viewdefs[$module][$platform]['view'][$view]['panels'] as $panel) {
            foreach ($panel['fields'] as $field) {
                if ((is_array($field) && isset($field['name']) && $field['name'] === $fieldName) ||
                    is_string($field) && $field === $fieldName) {
                    return $field;
                }
            }
        }

        return null;
    }

    /**
     * Save viewdefs to file and log about it.
     * @param string $name Main variable name in defs file.
     * @param array $defs View definition.
     * @param string $file Name of the file.
     */
    public function saveViewDefsToFile($name, $defs, $file)
    {
        write_array_to_file($name, $defs, $file);
        $this->log("Fixed time-reminder fields in $file");
    }
}
