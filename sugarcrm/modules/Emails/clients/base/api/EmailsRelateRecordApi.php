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

require_once 'clients/base/api/RelateRecordApi.php';

class EmailsRelateRecordApi extends RelateRecordApi
{
    /**
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        return array(
            'createRelatedRecord' => array(
                'reqType' => 'POST',
                'path' => array('Emails', '?', 'link', '?'),
                'pathVars' => array('module', 'record', '', 'link_name'),
                'method' => 'createRelatedRecord',
                'shortHelp' => 'Create a single record and relate it to this module',
                'longHelp' => 'include/api/help/module_record_link_link_name_post_help.html',
            ),
        );
    }

    /**
     * When creating a new EmailAddresses record with an email address that already exists, the call is rerouted to link
     * the existing EmailAddresses record instead.
     *
     * {@inheritdoc}
     */
    public function createRelatedRecord($api, $args)
    {
        $primaryBean = $this->loadBean($api, $args);
        list($linkName) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view', 'create');
        $module = $primaryBean->$linkName->getRelatedModuleName();

        if ($module === 'EmailAddresses' && !empty($args['email_address'])) {
            $guid = $this->getEmailAddressId($args['email_address']);

            if (!empty($guid)) {
                $args = array_merge($args, array('remote_id' => $guid));
                return $this->createRelatedLink($api, $args);
            }
        }

        return parent::createRelatedRecord($api, $args);
    }

    /**
     * Given an email address, this method returns the ID of that email address when it already exists or an empty
     * string when it does not exist.
     *
     * @see SugarEmailAddress:getGuid()
     * @return string
     */
    protected function getEmailAddressId($address)
    {
        $sea = new SugarEmailAddress();
        return $sea->getGuid($address);
    }
}
