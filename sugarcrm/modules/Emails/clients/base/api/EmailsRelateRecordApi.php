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

class EmailsRelateRecordApi extends RelateRecordApi
{
    /**
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        return [
            'createRelatedRecord' => [
                'reqType' => 'POST',
                'path' => ['Emails', '?', 'link', '?'],
                'pathVars' => ['module', 'record', '', 'link_name'],
                'method' => 'createRelatedRecord',
                'shortHelp' => 'Create a single record and relate it to an email',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_link_link_name_post_help.html',
                'exceptions' => [
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionNotFound',
                ],
            ],
            'createRelatedLink' => [
                'reqType' => 'POST',
                'path' => ['Emails', '?', 'link', '?', '?'],
                'pathVars' => ['module', 'record', '', 'link_name', 'remote_id'],
                'method' => 'createRelatedLink',
                'shortHelp' => 'Relates an existing record to an email',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_link_link_name_remote_id_post_help.html',
                'exceptions' => [
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionNotFound',
                ],
            ],
            'createRelatedLinks' => [
                'reqType' => 'POST',
                'path' => ['Emails', '?', 'link'],
                'pathVars' => ['module', 'record', ''],
                'method' => 'createRelatedLinks',
                'shortHelp' => 'Relates existing records to an email',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_link_post_help.html',
                'exceptions' => [
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionNotFound',
                ],
            ],
            'deleteRelatedLink' => [
                'reqType' => 'DELETE',
                'path' => ['Emails', '?', 'link', '?', '?'],
                'pathVars' => ['module', 'record', '', 'link_name', 'remote_id'],
                'method' => 'deleteRelatedLink',
                'shortHelp' => 'Deletes a relationship between an email and another record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_link_link_name_remote_id_delete_help.html',
                'exceptions' => [
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionNotFound',
                ],
            ],
            'createRelatedLinksFromRecordList' => [
                'reqType' => 'POST',
                'path' => ['Emails', '?', 'link', '?', 'add_record_list', '?'],
                'pathVars' => ['module', 'record', '', 'link_name', '', 'remote_id'],
                'method' => 'createRelatedLinksFromRecordList',
                'shortHelp' => 'Relates existing records from a record list to an email',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_links_from_recordlist_post_help.html',
                'exceptions' => [
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionNotFound',
                ],
            ],
        ];
    }

    /**
     * Creating records for the links from the from, to, cc, and bcc collection fields is not supported. Only existing
     * records can be added for these links, with the exception of email_addresses_from, email_addresses_to,
     * email_addresses_cc, and email_addresses_bcc.
     *
     * When creating a new EmailAddresses record with an email address that already exists, the call is rerouted to link
     * the existing EmailAddresses record instead.
     *
     * {@inheritdoc}
     * @throws SugarApiExceptionNotAuthorized
     */
    public function createRelatedRecord(ServiceBase $api, array $args)
    {
        $primaryBean = $this->loadBean($api, $args);
        list($linkName) = $this->checkRelatedSecurity($api, $args, $primaryBean, 'view', 'create');
        $relatedModuleName = $primaryBean->$linkName->getRelatedModuleName();

        static $allowed = [
            'email_addresses_from',
            'email_addresses_to',
            'email_addresses_cc',
            'email_addresses_bcc',
        ];

        foreach (['from', 'to', 'cc', 'bcc'] as $field) {
            $links = VardefManager::getLinkFieldsForCollection(
                $primaryBean->getModuleName(),
                $primaryBean->getObjectName(),
                $field
            );

            if (in_array($linkName, $links) && !in_array($linkName, $allowed)) {
                throw new SugarApiExceptionNotAuthorized("Cannot create related records for link: {$linkName}");
            }
        }

        if ($relatedModuleName === 'EmailAddresses' && !empty($args['email_address'])) {
            $guid = $this->getEmailAddressId($args['email_address']);

            if (!empty($guid)) {
                $args = array_merge($args, ['remote_id' => $guid]);
                return $this->createRelatedLink($api, $args);
            }
        }

        return parent::createRelatedRecord($api, $args);
    }

    /**
     * Prevents existing Notes records from being linked as attachments.
     *
     * {@inheritdoc}
     * @throws SugarApiExceptionNotAuthorized
     */
    public function createRelatedLinks(
        ServiceBase $api,
        array $args,
        $securityTypeLocal = 'view',
        $securityTypeRemote = 'view'
    ) {
        if ($args['link_name'] === 'attachments') {
            throw new SugarApiExceptionNotAuthorized('Cannot link existing attachments');
        }

        return parent::createRelatedLinks($api, $args, $securityTypeLocal, $securityTypeRemote);
    }

    /**
     * Prevents existing Notes records from being linked as attachments.
     *
     * {@inheritdoc}
     * @throws SugarApiExceptionNotAuthorized
     */
    public function createRelatedLinksFromRecordList(ServiceBase $api, array $args)
    {
        if ($args['link_name'] === 'attachments') {
            throw new SugarApiExceptionNotAuthorized('Cannot link existing attachments');
        }

        return parent::createRelatedLinksFromRecordList($api, $args);
    }

    /**
     * The sender cannot be removed. Replace the sender with a different sender instead.
     *
     * {@inheritdoc}
     * @throws SugarApiExceptionNotAuthorized
     */
    public function deleteRelatedLink(ServiceBase $api, array $args)
    {
        $links = VardefManager::getLinkFieldsForCollection('Emails', BeanFactory::getObjectName('Emails'), 'from');

        if (in_array($args['link_name'], $links)) {
            throw new SugarApiExceptionNotAuthorized('The sender cannot be removed');
        }

        return parent::deleteRelatedLink($api, $args);
    }

    /**
     * Given an email address, this method returns the ID of that email address when it already exists or an empty
     * string when it does not exist.
     *
     * @see SugarEmailAddress:getGuid()
     * @param string $address
     * @return string
     */
    protected function getEmailAddressId($address)
    {
        $sea = new SugarEmailAddress();
        return $sea->getGuid($address);
    }
}
