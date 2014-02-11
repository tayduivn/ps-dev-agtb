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

class SugarUpgradePopulateEmailLinkTypePreference extends UpgradeScript
{
    public $order = 2200;
    public $type = self::UPGRADE_DB;


    /**
     * Run the Upgrade Task
     *
     * Set the user preference for email link type to 'mailto' if the system configuration is not properly set
     */
    public function run()
    {
        if (!version_compare($this->from_version, '7.2.0', "<")) {
            return;
        }

        $user  = BeanFactory::getBean('Users');
        $users = get_user_array(false);

        foreach ($users as $userId => $userName) {
            $user->retrieve($userId);
            $emailClientPreference = $user->getPreference('email_link_type');

            if ($emailClientPreference == 'sugar') {
                $mailerPreferenceStatus = OutboundEmailConfigurationPeer::getMailConfigurationStatusForUser($user, 'sugar');
                if ($mailerPreferenceStatus != OutboundEmailConfigurationPeer::STATUS_VALID_CONFIG) {
                    $user->setPreference('email_link_type', 'mailto');
                }
                $user->savePreferencesToDB();
            }
        }
    }
}
