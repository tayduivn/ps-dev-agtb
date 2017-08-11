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
                'minVersion' => 11,
                'exceptions' => array(
                    'SugarApiExceptionInvalidParameter',
                    'SugarApiExceptionMissingParameter',
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionNotFound',
                    'SugarApiException',
                    'SugarApiExceptionError',
                ),
            ),
            'retrieve' => array(
                'reqType' => 'GET',
                'path' => array('Emails', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'retrieveRecord',
                'shortHelp' => 'Returns a single Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_get_help.html',
                'exceptions' => array(
                    'SugarApiExceptionMissingParameter',
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionNotFound',
                ),
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('Emails', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'updateRecord',
                'shortHelp' => 'This method updates an Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_put_help.html',
                'minVersion' => 11,
                'exceptions' => array(
                    'SugarApiExceptionInvalidParameter',
                    'SugarApiExceptionMissingParameter',
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionNotFound',
                    'SugarApiException',
                    'SugarApiExceptionError',
                ),
            ),
        );
    }

    /**
     * Prevents the creation of a bean when the state transition is invalid. Sends the email when the state is "Ready."
     *
     * {@inheritdoc}
     */
    public function createRecord(ServiceBase $api, array $args)
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

        if ($args['state'] === Email::STATE_DRAFT && isset($args['from'])) {
            throw new SugarApiExceptionNotAuthorized('Not allowed to edit field from when saving a draft');
        }

        $result = parent::createRecord($api, $args);

        if ($isReady) {
            $loadArgs = array('module' => 'Emails', 'record' => $result['id']);
            $email = $this->loadBean($api, $loadArgs, 'save', array('source' => 'module_api'));

            try {
                $this->sendEmail($email);
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
    public function updateRecord(ServiceBase $api, array $args)
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

        if ($bean->state === Email::STATE_DRAFT && isset($args['from'])) {
            throw new SugarApiExceptionNotAuthorized('Not allowed to edit field from when saving a draft');
        }

        $result = parent::updateRecord($api, $args);

        if ($isReady) {
            $email = $this->loadBean($api, $args, 'save', array('source' => 'module_api'));
            $this->sendEmail($email);
            $result = $this->formatBeanAfterSave($api, $args, $email);
        }

        return $result;
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
     * @param SugarBean $email
     * @throws SugarApiException
     * @throws SugarApiExceptionError
     */
    protected function sendEmail(SugarBean $email)
    {
        try {
            $config = null;
            $oe = null;

            if (empty($email->outbound_email_id)) {
                $seed = BeanFactory::newBean('OutboundEmail');
                $q = new SugarQuery();
                $q->from($seed);
                $q->where()->in('type', [OutboundEmail::TYPE_SYSTEM, OutboundEmail::TYPE_SYSTEM_OVERRIDE]);
                // There should only be one system or system-override account that is accessible. The admin can actually
                // access both a system and system-override account. Sorting in descending order by type and setting a
                // limit guarantees that the system-override account is prioritized when finding the default record to
                // use.
                $q->orderBy('type');
                $q->limit(1);
                $beans = $seed->fetchFromQuery($q, ['id']);

                if (!empty($beans)) {
                    $bean = array_shift($beans);
                    $email->outbound_email_id = $bean->id;
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
            $this->relateRecordApi = new EmailsRelateRecordApi();
        }

        return $this->relateRecordApi;
    }
}
