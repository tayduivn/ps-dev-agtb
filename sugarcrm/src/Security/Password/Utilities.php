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

namespace Sugarcrm\Sugarcrm\Security\Password;

class Utilities
{
    /**
     * Inserts values into db when a reset pwd link is created
     * for regular user and portal users
     * @param array $values
     * @return bool
     */
    public static function insertIntoUserPwdLink(array $values)
    {
        // we don't want to insert into the db if any of these fields are empty
        $requiredParams = [
            'guid',
            'bean_id',
            'name',
        ];
        foreach ($requiredParams as $param) {
            if (empty($values[$param])) {
                \LoggerManager::getLogger()->fatal('Could not insert into `users_password_link` because' . $param .
                    ' is empty.');
                return false;
            }
        }

        if (empty($values['platform'])) {
            $values['platform'] = 'base';
        }

        if (empty($values['bean_type'])) {
            $values['bean_type'] = 'Users';
        }

        $db = \DBManagerFactory::getInstance();
        $query = sprintf(
            "INSERT INTO users_password_link (id, bean_id, bean_type, username, date_generated, platform)
        VALUES(%s, %s, %s, %s, %s, %s) ",
            $db->quoted($values['guid']),
            $db->quoted($values['bean_id']),
            $db->quoted($values['bean_type']),
            $db->quoted($values['name']),
            $db->quoted(\TimeDate::getInstance()->nowDb()),
            $db->quoted($values['platform'])
        );

        return $db->query($query);
    }

    /**
     * Creates an Email Template for Portal Password Reset Email
     * @param string $teamId
     * @return null|\SugarBean Email Teamplate
     */
    public static function addPortalPasswordSeedData(string $teamId, $mod_strings)
    {
        $portalLinkTpl = \BeanFactory::getBean('EmailTemplates');
        $portalLinkTpl->name = $mod_strings['portal_forgot_password_email_link']['name'];
        $portalLinkTpl->description = $mod_strings['portal_forgot_password_email_link']['description'];
        $portalLinkTpl->subject = $mod_strings['portal_forgot_password_email_link']['subject'];
        $portalLinkTpl->body = $mod_strings['portal_forgot_password_email_link']['txt_body'];
        $portalLinkTpl->body_html = $mod_strings['portal_forgot_password_email_link']['body'];
        $portalLinkTpl->team_id = $teamId;
        $portalLinkTpl->published = 'off';
        $portalLinkTpl->type = 'system';
        $portalLinkTpl->text_only = 1;
        $portalLinkTpl->id = 'f29f5864-bc90-11e9-82c9-a45e60e684a5';
        $portalLinkTpl->new_with_id = true;
        return $portalLinkTpl->save();
    }
}
