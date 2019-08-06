<?php
declare(strict_types=1);
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

use Sugarcrm\Sugarcrm\Util\Uuid;
use Sugarcrm\Sugarcrm\Security\Password\Utilities;

/**
 * Class PortalPasswordApi
 *
 * Sends email to reset portal password
 */
class PortalPasswordApi extends SugarApi
{
    public function registerApiRest()
    {
        return [
            'resetEmailPortalPassword' => [
                'reqType' => 'GET',
                'path' => ['password', 'resetemail'],
                'pathVars' => [],
                'method' => 'resetEmailPortalPassword',
                'shortHelp' => 'This method sends email requests to reset passwords for Portal users',
                'longHelp' => 'include/api/help/portal_password_reset_email_get_help.html',
                'noLoginRequired' => true,
                'ignoreSystemStatusError' => true,
                'minVersion' => '11.6',
            ],
        ];
    }

    /**
     * Creates url and sends email to user to reset password for Portal
     * @param ServiceBase $api
     * @param array $args
     * @return bool
     * @throws SugarApiExceptionRequestMethodFailure
     * @throws SugarApiExceptionMissingParameter
     */
    public function resetEmailPortalPassword(ServiceBase $api, array $args) : bool
    {
        $this->requireArgs($args, ['username']);

        $contactBean = $this->getBean('Contacts');
        $contactBean->disable_row_level_security = true;

        // get contact's id
        $query = $this->getSugarQuery();
        $query->select(['id']);
        $query->from($contactBean);
        $query->where()->equals('portal_name', $args['username']);

        $row = $query->getOne();

        if (!empty($row)) {
            $contactBean->retrieve($row);

            // get the password template
            $pwdSetting = $this->getConfigValue('portalpasswordsetting');
            $templateID = $pwdSetting['lostpasswordtmpl'];
            $platform = $api->platform;

            return $this->sendEmail($templateID, $contactBean, $platform);
        }

        return false;
    }

    /**
     * Wrapper to get a new SugarBean
     *
     * @param string $module The module name
     * @return null|SugarBean
     * @throws SugarApiExceptionNotFound
     */
    public function getBean(string $module) : SugarBean
    {
        return BeanFactory::getBean($module);
    }

    /**
     * Wrapper to get a new SugarQuery
     *
     * @return SugarQuery
     */
    public function getSugarQuery() : SugarQuery
    {
        return new SugarQuery();
    }


    /**
     * Returns values for attributes from sugar config
     * @param string $key Sugar config attribute
     * @return mixed
     */
    public function getConfigValue(string $key)
    {
        return getValueFromConfig($key);
    }

    /**
     * Creates reset url and saves to db
     * @param SugarBean $contactBean
     * @return string|bool on failure
     */
    private function createResetLink(SugarBean $contactBean, string $platform)
    {
        $guid = Uuid::uuid1();

        // create a url with new guid
        $url = prependSiteURL('/portal/#resetpassword/'.$guid);
        $values = [
            'guid' => $guid,
            'bean_id' => $contactBean->id,
            'bean_type' => $contactBean->module_name,
            'name' => $contactBean->portal_name,
            'platform' => $platform,
        ];

        if (!empty(Utilities::insertIntoUserPwdLink($values))) {
            return $url;
        }

        return false;
    }

    /**
     * Sends link to user. Does not support HTML body due to security reasons.
     * @param string $templateId Email Template id
     * @param SugarBean $contactBean Contact bean who wants reset the password
     * @return bool
     * @throws SugarApiException
     */
    public function sendEmail(string $templateId, SugarBean $contactBean, string $platform) : bool
    {
        $result = false;

        if (empty($templateId)) {
            LoggerManager::getLogger()->fatal('No Email Template available for Portal Reset Password');
            return $result;
        }

        // get the email template
        $emailTemplate = BeanFactory::getBean('EmailTemplates', $templateId, ['disable_row_level_security' => true]);

        if (empty($emailTemplate->id)) {
            throw new SugarApiException('No Email Template');
        }

        $resetLink = $this->createResetLink($contactBean, $platform);

        if (empty($resetLink)) {
            return $result;
        }
        // replace the placeholder with the actual url
        $emailTemplate->body = str_replace('$portal_user_link_guid', $resetLink, $emailTemplate->body);

        try {
            $mailer = MailerFactory::getSystemDefaultMailer();
            $mailTransmissionProtocol = $mailer->getMailTransmissionProtocol();

            // set subject
            $mailer->setSubject($emailTemplate->subject);

            // set plain-text body
            $mailer->setTextBody($emailTemplate->body);

            // get recipient's email address
            $emailAdrs = $contactBean->emailAddress->getPrimaryAddress($contactBean);

            if (!empty($emailAdrs)) {
                // add the recipient
                $mailer->addRecipientsTo(new EmailIdentity($emailAdrs, $contactBean->full_name));

                // not a bad idea to set messageID for the Mailer
                $emailId = Uuid::uuid1();
                $mailer->setMessageId($emailId);

                // if send doesn't raise an exception, set the result status to true
                $mailer->send();
                $result = true;
            } else {
                throw new MailerException('There are no recipients', MailerException::FailedToSend);
            }
        } catch (MailerException $me) {
            // throw the exceptions
            $message = $me->getMessage();

            switch ($me->getCode()) {
                case MailerException::FailedToConnectToRemoteServer:
                    LoggerManager::getLogger()->fatal('Email Reminder: error sending email, system smtp server is not set');
                    break;
                default:
                    LoggerManager::getLogger()->fatal('Email Reminder: error sending e-mail (method: '.
                        $mailTransmissionProtocol .'), (error: '.$message .')');
                    break;
            }
            throw new SugarApiException(translate('LBL_PASSWORD_RESET_EMAIL_FAIL'));
        }

        return $result;
    }
}
