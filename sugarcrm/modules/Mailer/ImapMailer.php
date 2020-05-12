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

use Laminas\Mail\Protocol\Imap;

class ImapMailer implements Inbound
{
    /**
     * @var string $username
     */
    private $username;

    /**
     * @var Mailbox $mailbox
     */
    private $mailbox;

    /**
     * @var string|null $password
     */
    private $password;

    /**
     * @var string|null $eapmId
     */
    private $eapmId;

    /**
     * @var Imap $client
     */
    private $client;

    public function __construct(Mailbox $mailbox, $username, $password = null, $eapmId = null)
    {
        $this->mailbox = $mailbox;
        $this->username = $username;
        $this->password = $password;
        $this->eapmId = $eapmId;

        $this->createConnection();
    }

    /**
     * Creates Laminas Mail object to use to connect to IMAP server
     */
    private function createConnection()
    {
        if (is_null($this->password) && is_null($this->eapmId)) {
            LoggerManager::getLogger()->error('ImapMailer requires a password for basic authentication or eapm_id for OAUTH connections');
            return;
        }

        $mailbox = $this->mailbox;
        $security = $mailbox->getSecurityProtocol();
        if ($security === 'none') {
            $security = false;
        }
        $this->client = new Imap($mailbox->getHost(), $mailbox->getPort(), $security);

        if (!empty($this->eapmId)) {
            $this->oauthConnect();
        }
    }

    /**
     * Authenticate via Oauth2
     */
    private function oauthConnect()
    {
        $token = $this->getAccessToken();
        $authString = (new XOAUTHEncoder($this->username, $token))->getOauth64();
        $authenticateParams = ['XOAUTH2', $authString];
        $this->client->sendRequest('AUTHENTICATE', $authenticateParams);
    }

    /**
     * Get the access token
     *
     * @return string
     */
    private function getAccessToken()
    {
        if (strpos($this->mailbox->getHost(), 'gmail') !== false) {
            $api = new ExtAPIGoogleEmail();
        } else {
            $api = new ExtAPIMicrosoftEmail();
        }
        $token = $api->getAccessToken($this->eapmId);

        if (empty($token)) {
            LoggerManager::getLogger()->error('Could not retrieve access token from EAPM bean: ' . $this->eapmId);
            return '';
        }
        return $token;
    }

    /**
     * Test if the connection was successful
     * @return bool
     */
    public function testSettings()
    {
        // Basic Auth
        if (empty($this->eapmId)) {
            return $this->client->login($this->username, $this->password);
        }

        // Oauth
        while (true) {
            $response = "";
            $is_plus = $this->client->readLine($response, '+', true);
            if ($is_plus) {
                // got an extra server challenge
                // Send empty client response.
                $this->client->sendRequest('');
            } else {
                if (preg_match('/^NO /i', $response) ||
                    preg_match('/^BAD /i', $response)) {
                    return false;
                } elseif (preg_match("/^OK /i", $response)) {
                    return true;
                }
            }
        }
    }

    /**
     * Gets a list of mailbox data from the current connection
     *
     * @return array
     */
    public function getMailboxes() : array
    {
        $boxes = [];
        if (!empty($this->client)) {
            $boxes = $this->client->listMailbox();
        }
        return $boxes;
    }
}
