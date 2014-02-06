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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 * Migrate portal settings
 */
class SugarUpgradePortalSettings extends UpgradeScript
{
    public $order = 2170;
    public $type = self::UPGRADE_DB;
    public $version = '7.1.5';

    public function run()
    {
        if (!$this->toFlavor('ent')) {
            return;
        }

        // only run this when coming from a version lower than 7.1.5
        if (version_compare($this->from_version, '7.1.5', '>=')) {
            return;
        }

        global $mod_strings;

        // Update portal setting name `displayModules` to `tab`
        $this->updatePortalTabsSetting();

        // Set portal setting `logLevel` to `ERROR`
        $fieldKey = 'logLevel';
        $fieldValue = 'ERROR';
        $admin = new Administration();
        if (!$admin->saveSetting('portal', $fieldKey, json_encode($fieldValue), 'support')) {
            $error = sprintf($this->mod_strings['ERROR_UW_PORTAL_CONFIG_DB'], 'portal', $fieldKey, $fieldValue);
            return $this->fail($error);
        }

        // Remove `portal_on` with platform equals to NULL
        $query = "DELETE FROM config WHERE category='portal' AND name='on' AND platform IS NULL";
        $this->db->query($query);

        // Remove `fieldsToDisplay` (# of fields displayed in detail view - not used anymore in 7.0)
        $query = "DELETE FROM config WHERE category='portal' AND name='fieldsToDisplay' AND platform='support'";
        $this->db->query($query);
    }

    /**
     * Migrate portal tab settings previously stored as:
     * `category` = 'portal', `platform` = 'support', `name` = 'displayModules'
     * to:
     * `category` = 'MySettings', `platform` = 'portal', `name` = 'tab'
     */
    public function updatePortalTabsSetting()
    {
        $admin = Administration::getSettings();
        $portalConfig = $admin->getConfigForModule('portal', 'support', true);

        if (empty($portalConfig['displayModules'])) {
            return;
        }

        // If Home does not exist we push Home in front of the array
        if (!in_array('Home', $portalConfig['displayModules'])) {
            array_unshift($portalConfig['displayModules'], 'Home');
        }

        if ($admin->saveSetting('MySettings', 'tab', json_encode($portalConfig['displayModules']), 'portal')) {
            // Remove old config setting `displayModules`
            $query = "DELETE FROM config WHERE category='portal' AND platform='support' AND name='displayModules'";
            $this->db->query($query);
        } else {
            $log = 'Error upgrading portal config var displayModules, ';
            $log .= 'orig: ' . $portalConfig['displayModules'] . ', ';
            $log .= 'json:' . json_encode($portalConfig['displayModules']);
            $this->log($log);
        }
    }
}
