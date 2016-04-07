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

require_once 'modules/OutboundEmailConfiguration/OutboundEmailConfigurationPeer.php';
require_once 'modules/Mailer/MailerFactory.php';

use Sugarcrm\Sugarcrm\Notification\Carrier\TransportInterface;

/**
 * Class CarrierEmailTransport.
 * Is used to send messages via Sugar System Mailer.
 */
class CarrierEmailTransport implements TransportInterface
{
    /**
     * Send message to a specified user by the means of Sugar System Mailer.
     *
     * @param string $recipient Sugar User email address.
     * @param array $message message pack for delivery.
     * @return bool true if message was sent, otherwise false.
     */
    public function send($recipient, $message)
    {
        if ($this->test() && (!empty($message['title']) || !empty($message['text']) || !empty($message['html']))) {
            try {
                $mailer = $this->getMailerFactory()->getSystemDefaultMailer();
                $mailer->addRecipientsTo(new EmailIdentity($recipient));
                if (!empty($message['title'])) {
                    $mailer->setSubject($message['title']);
                }
                if (!empty($message['text'])) {
                    $mailer->setTextBody($message['text']);
                }
                if (!empty($message['html'])) {
                    $mailer->setHtmlBody($message['html']);
                }
                $mailer->send();
                return true;
            } catch (MailerException $me) {
                $message = $me->getMessage();
                $GLOBALS["log"]->warn("Email Carrier Transport: error sending e-mail. Error: {$message})");
            }
        }
        return false;
    }

    /**
     * Test if System Default Outbound Mailer is configured and available.
     *
     * @return bool true if available, otherwise false.
     */
    protected function test()
    {
        $configuration = $this->getOutboundEmailConfigurationPeer();
        $outboundMailConfig = $configuration->getSystemDefaultMailConfiguration();
        return $configuration->isMailConfigurationValid($outboundMailConfig);
    }

    /**
     * Factory method to mock MailerFactory
     *
     * @return MailerFactory
     */
    protected function getMailerFactory()
    {
        return new MailerFactory();
    }

    /**
     * Factory method to mock OutboundEmailConfigurationPeer
     *
     * @return OutboundEmailConfigurationPeer
     */
    protected function getOutboundEmailConfigurationPeer()
    {
        return new OutboundEmailConfigurationPeer();
    }
}
