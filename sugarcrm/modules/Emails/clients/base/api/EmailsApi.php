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

require_once 'clients/base/api/ModuleApi.php';

class EmailsApi extends ModuleApi
{
    /**
     * Wildcard state value.
     *
     * @var string
     */
    const STATE_ANY = '*';

    /**
     * The valid transitions for an Emails record's state.
     *
     * @var array
     */
    private $validStateTransitions = array(
        'create' => array(
            array(
                'from' => self::STATE_ANY,
                'to' => array(
                    Email::STATE_READY,
                    Email::STATE_DRAFT,
                    Email::STATE_ARCHIVED,
                ),
            ),
        ),
        'update' => array(
            array(
                'from' => Email::STATE_DRAFT,
                'to' => array(
                    Email::STATE_DRAFT,
                    // The draft is ready to be sent.
                    Email::STATE_READY,
                ),
            ),
            array(
                'from' => Email::STATE_ARCHIVED,
                'to' => array(
                    // Allows for changing teams or the assigned user, etc.
                    Email::STATE_ARCHIVED,
                ),
            ),
        ),
    );

    /**
     * The fields `type` and `status` are disabled on create and update. The field `id` is disabled on create.
     *
     * All sender links are disabled on update, as the sender cannot be changed. For emails in the "Draft," or "Ready"
     * state, the sender is always the current user. For emails in the "Archived" state, the sender is immutable.
     *
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->disabledCreateFields = array_merge($this->disabledCreateFields, array('id', 'type', 'status'));
        $this->disabledUpdateFields = array_merge(
            $this->disabledUpdateFields,
            array('type', 'status'),
            VardefManager::getLinkFieldsForCollection('Emails', BeanFactory::getObjectName('Emails'), 'from')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        return array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('Emails'),
                'pathVars' => array('module'),
                'method' => 'createRecord',
                'shortHelp' => 'This method creates a new Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_post_help.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('Emails', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'updateRecord',
                'shortHelp' => 'This method updates an Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_put_help.html',
            ),
        );
    }

    /**
     * Prevents the creation of a bean when the state transition is invalid. Sends the email when the state is "Ready."
     *
     * The current user is always used as the sender for emails in the "Draft" or "Ready" states.
     *
     * {@inheritdoc}
     */
    public function createRecord(ServiceBase $api, $args)
    {
        $this->requireArgs($args, array('state'));

        if (!$this->isValidStateTransition('create', static::STATE_ANY, $args['state'])) {
            $message = "State transition to {$args['state']} is invalid for creating an email";
            throw new SugarApiExceptionInvalidParameter($message);
        }

        $isReady = false;

        if ($args['state'] === Email::STATE_READY) {
            $isReady = true;
            $args['state'] = Email::STATE_DRAFT;
        }

        if ($args['state'] === Email::STATE_DRAFT) {
            $fromLinks = VardefManager::getLinkFieldsForCollection(
                'Emails',
                BeanFactory::getObjectName('Emails'),
                'from'
            );

            // Drop any submitted senders. The current user will be added as the sender.
            foreach ($fromLinks as $link) {
                unset($args[$link]);
            }
        }

        $result = parent::createRecord($api, $args);

        if ($isReady) {
            $loadArgs = array('module' => 'Emails', 'record' => $result['id']);
            $email = $this->loadBean($api, $loadArgs, 'save', array('source' => 'module_api'));

            try {
                $this->sendEmail($api, $email);
                $result = $this->formatBeanAfterSave($api, $args, $email);
            } catch (Exception $e) {
                $email->delete();
                throw $e;
            }
        }

        return $result;
    }

    /**
     * Prevents the update of a bean when the state transition is invalid. Sends the email when the state is "Ready."
     *
     * {@inheritdoc}
     */
    public function updateRecord(ServiceBase $api, $args)
    {
        $api->action = 'view';
        $this->requireArgs($args, array('module', 'record'));

        $bean = $this->loadBean($api, $args, 'save', array('source' => 'module_api'));
        $api->action = 'save';
        $isReady = false;

        if (isset($args['state'])) {
            if (!$this->isValidStateTransition('update', $bean->state, $args['state'])) {
                $message = "State transition from {$bean->state} to {$args['state']} is invalid for an email";
                throw new SugarApiExceptionInvalidParameter($message);
            }

            if ($args['state'] === Email::STATE_READY) {
                $isReady = true;
                unset($args['state']);
            }
        }

        $result = parent::updateRecord($api, $args);

        if ($isReady) {
            $email = $this->loadBean($api, $args, 'save', array('source' => 'module_api'));
            $this->sendEmail($api, $email);
            $result = $this->formatBeanAfterSave($api, $args, $email);
        }

        return $result;
    }

    /**
     * Prevents existing Notes records from being linked as attachments.
     *
     * {@inheritdoc}
     */
    protected function linkRelatedRecords(
        ServiceBase $service,
        SugarBean $bean,
        array $ids,
        $securityTypeLocal = 'view',
        $securityTypeRemote = 'view'
    ) {
        unset($ids['attachments']);
        parent::linkRelatedRecords($service, $bean, $ids, $securityTypeLocal, $securityTypeRemote);
    }

    /**
     * The sender cannot be removed.
     *
     * {@inheritdoc}
     */
    protected function unlinkRelatedRecords(ServiceBase $service, SugarBean $bean, array $ids)
    {
        $links = VardefManager::getLinkFieldsForCollection($bean->module_dir, $bean->object_name, 'from');

        foreach ($links as $linkName) {
            unset($ids[$linkName]);
        }

        parent::unlinkRelatedRecords($service, $bean, $ids);
    }

    /**
     * Prepares attachments for being related. This includes patching the related record arguments for attachments to
     * contain the data necessary for creating the requisite Notes records, as well as placing the file.
     *
     * Creating records for the links from the from, to, cc, and bcc collection fields is not supported. Only existing
     * records can be added for these links, with the exception of email_addresses_from, email_addresses_to,
     * email_addresses_cc, and email_addresses_bcc.
     *
     * {@inheritdoc}
     */
    protected function createRelatedRecords(ServiceBase $service, SugarBean $bean, array $data)
    {
        $relate = array();
        $skip = array();
        $doNotSkip = array(
            'email_addresses_from',
            'email_addresses_to',
            'email_addresses_cc',
            'email_addresses_bcc',
        );

        foreach (array('from', 'to', 'cc', 'bcc') as $field) {
            $links = VardefManager::getLinkFieldsForCollection($bean->module_dir, $bean->object_name, $field);

            foreach ($links as $linkName) {
                if (!in_array($linkName, $doNotSkip)) {
                    $skip[] = $linkName;
                }
            }
        }

        foreach ($data as $linkName => $records) {
            switch ($linkName) {
                case in_array($linkName, $skip):
                    // Creating records over these links is not supported.
                    break;
                default:
                    $relate[$linkName] = $records;
            }
        }

        parent::createRelatedRecords($service, $bean, $relate);
    }

    /**
     * Is the supplied state transition valid?
     *
     * @param string $operation
     * @param string $fromState
     * @param string $toState
     * @return boolean
     */
    protected function isValidStateTransition($operation, $fromState, $toState)
    {
        $transitions = $this->validStateTransitions[$operation];

        foreach ($transitions as $transition) {
            if (in_array($transition['from'], array(self::STATE_ANY, $fromState)) &&
                in_array($toState, $transition['to'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send the email.
     *
     * The system configuration is used if no configuration is specified on the email. An error will occur if the
     * application is not configured correctly to send email.
     *
     * @param ServiceBase $service
     * @param SugarBean $email
     * @throws SugarApiException
     */
    protected function sendEmail(ServiceBase $service, SugarBean $email)
    {
        try {
            $config = null;
            $oe = null;

            if (empty($email->outbound_email_id)) {
                $args = [
                    'module' => 'OutboundEmail',
                    'filter' => [
                        [
                            'type' => [
                                '$in' => [
                                    'system',
                                    'system-override',
                                ],
                            ],
                        ],
                    ],
                    'fields' => 'id',
                    // There should only be one system or system-override account that is accessible. The admin can
                    // actually access both a system and system-override account. Sorting and setting a limit guarantees
                    // that the system-override account is prioritized when finding the default record to use.
                    'order_by' => 'type:desc',
                    'max_num' => 1,
                ];
                $api = new OutboundEmailFilterApi();
                $data = $api->filterList($service, $args);

                if (!empty($data['records'])) {
                    $record = array_shift($data['records']);
                    $email->outbound_email_id = $record['id'];
                }
            }

            if (!empty($email->outbound_email_id)) {
                $oe = BeanFactory::retrieveBean('OutboundEmail', $email->outbound_email_id);
            }

            if ($oe) {
                if ($oe->isConfigured()) {
                    $config = OutboundEmailConfigurationPeer::buildOutboundEmailConfiguration(
                        $GLOBALS['current_user'],
                        [
                            'config_id' => $oe->id,
                            'config_type' => $oe->type,
                            'from_email' => $oe->email_address,
                            'from_name' => $oe->name,
                        ],
                        $oe
                    );
                } else {
                    throw new MailerException(
                        'The configuration for sending email is invalid',
                        MailerException::InvalidConfiguration
                    );
                }
            }

            if (empty($config)) {
                throw new MailerException(
                    'Could not find a configuration for sending email',
                    MailerException::InvalidConfiguration
                );
            }

            $email->sendEmail($config);
        } catch (MailerException $e) {
            switch ($e->getCode()) {
                case MailerException::FailedToSend:
                case MailerException::FailedToConnectToRemoteServer:
                case MailerException::InvalidConfiguration:
                    throw new SugarApiException(
                        $e->getUserFriendlyMessage(),
                        null,
                        'Emails',
                        451,
                        'smtp_server_error'
                    );
                case MailerException::InvalidHeader:
                case MailerException::InvalidEmailAddress:
                case MailerException::InvalidAttachment:
                case MailerException::FailedToTransferHeaders:
                case MailerException::ExecutableAttachment:
                    throw new SugarApiException(
                        $e->getUserFriendlyMessage(),
                        null,
                        'Emails',
                        451,
                        'smtp_payload_error'
                    );
                default:
                    throw new SugarApiExceptionError($e->getUserFriendlyMessage());
            }
        } catch (Exception $e) {
            throw new SugarApiExceptionError('Failed to send the email: ' . $e->getMessage());
        }
    }

    /**
     * EmailsApi needs an extended version of {@link RelateRecordApi} that is specific to Emails.
     *
     * @return EmailsRelateRecordApi
     */
    protected function getRelateRecordApi()
    {
        if (!$this->relateRecordApi) {
            require_once 'modules/Emails/clients/base/api/EmailsRelateRecordApi.php';
            $this->relateRecordApi = new EmailsRelateRecordApi();
        }

        return $this->relateRecordApi;
    }
}
