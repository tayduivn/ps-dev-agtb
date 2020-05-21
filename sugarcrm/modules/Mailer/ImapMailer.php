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

use Laminas\Mail;

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
     * @var Laminas\Mail\Protocol\Imap $client
     */
    private $client;

    /**
     * @var Laminas\Mail\Storage\Imap $storage
     */
    private $storage;

    /**
     * Save the fetched messages so we don't fetch every time
     * @var array $messageCache
     */
    private $messageCache = [];

    /**
     * ImapMailer constructor. Creates Imap instance and authenticates via basic auth or Oauth
     * @param Mailbox $mailbox
     * @param $username
     * @param null $password
     * @param null $eapmId
     */
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
        $this->client = new Mail\Protocol\Imap($mailbox->getHost(), $mailbox->getPort(), $security);

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

    /**
     * Search a mailbox for messages. Criteria must be passed as an array. Mailbox must be selected before calling.
     * @param array $criteria
     * @return array|bool|null
     */
    public function search(array $criteria)
    {
        return $this->client->search($criteria);
    }

    /**
     * Select the mailbox on server
     *
     * @param string $mailbox
     */
    public function selectMailbox(string $mailbox)
    {
        $this->client->select($mailbox);
        $this->messageCache = [];
    }

    /**
     * Gets the Uid for a message or returns message number if mailbox
     * doesn't support Uids.
     * @param $messageNum
     * @return string
     */
    public function getMessageUid($messageNum): string
    {
        $storage = $this->getStorage();
        return $storage->getUniqueId($messageNum);
    }

    /**
     * Gets the message number (sequence number) of a message from its Uid
     * @param int $uid
     * @return int|string
     */
    public function getMessageNum(int $uid)
    {
        $storage = $this->getStorage();
        return $storage->getNumberByUniqueId($uid);
    }

    /**
     * Gets a message object from Uid. Mailbox needs to be selected beforehand.
     * Caches the message so we don't download it multiple times.
     * @param int $uid
     * @return Mail\Storage\Message
     */
    public function getMessageFromId(int $uid) : Mail\Storage\Message
    {
        if (isset($this->messageCache[$uid])) {
            return $this->messageCache[$uid];
        }
        $storage = $this->getStorage();
        $message = $storage->getMessage($uid);
        $this->messageCache = [];
        $this->messageCache[$uid] = $message;

        return $message;
    }

    /**
     * Gets all headers in array form
     * @param int $uid
     * @return array
     */
    public function getHeaders(int $uid)
    {
        $message = $this->getMessageFromId($uid);
        return $message->getHeaders()->toArray();
    }

    /**
     * Gets headers for message as a string
     * @param int $uid
     * @return array|string
     */
    public function getRawHeaders(int $uid)
    {
        $storage = $this->getStorage();
        return $storage->getRawHeader($uid);
    }

    /**
     * Checks if a message has a flag.
     * @param string $flag
     * @param int $uid
     * @return bool
     */
    public function hasFlag(string $flag, int $uid)
    {
        $message = $this->getMessageFromId($uid);
        $flag = $this->getNormalizedFlagName($flag);
        return $message->hasFlag($flag);
    }

    /**
     * Set or remove a flag from the email
     * @param int $uid
     * @param string $flag see getNormalizedFlagName for flag names
     * @param bool $add `true` to add a flag, `false` to remove
     */
    public function setFlag(int $uid, string $flag, bool $add)
    {
        $message = $this->getMessageFromId($uid);
        $flags = $message->getFlags();
        $flag = $this->getNormalizedFlagName($flag);
        if ($add) {
            $flags[] = $flag;
        } else {
            unset($flags[$flag]);
        }

        $storage = $this->getStorage();
        $storage->setFlags($uid, $flags);
    }

    /**
     * {@inheritDoc}
     */
    public function getSubject($uid): string
    {
        return $this->getHeaderAsString($uid, 'Subject');
    }

    /**
     * {@inheritDoc}
     */
    public function getFrom($uid): string
    {
        return $this->getHeaderAsString($uid, 'From');
    }

    /**
     * {@inheritDoc}
     */
    public function getTo($uid): string
    {
        return $this->getHeaderAsString($uid, 'To');
    }

    /**
     * {@inheritDoc}
     */
    public function getCc($uid) : string
    {
        return $this->getHeaderAsString($uid, 'CC');
    }

    /**
     * {@inheritDoc}
     */
    public function getBcc($uid) : string
    {
        return $this->getHeaderAsString($uid, 'BCC');
    }

    /**
     * {@inheritDoc}
     */
    public function getReplyTo($uid) : string
    {
        return $this->getHeaderAsString($uid, 'Reply-To');
    }

    /**
     * Gets the header if available and returns it formatted as a string
     * @param int $uid
     * @param string $type
     * @return string
     */
    public function getHeaderAsString(int $uid, string $type) : string
    {
        $message = $this->getMessageFromId($uid);
        if (isset($message->$type)) {
            return $message->getHeader($type, 'string');
        }
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getFromAddress(int $uid) : array
    {
        $message = $this->getMessageFromId($uid);
        return $this->getAddressesFromHeader($message, 'From');
    }

    /**
     * {@inheritDoc}
     */
    public function getToAddresses(int $uid) : array
    {
        $message = $this->getMessageFromId($uid);
        return $this->getAddressesFromHeader($message, 'To');
    }

    /**
     * {@inheritDoc}
     */
    public function getCcAddresses(int $uid) : array
    {
        $message = $this->getMessageFromId($uid);
        return $this->getAddressesFromHeader($message, 'CC');
    }

    /**
     * {@inheritDoc}
     */
    public function getBccAddresses(int $uid) : array
    {
        $message = $this->getMessageFromId($uid);
        return $this->getAddressesFromHeader($message, 'BCC');
    }

    /**
     * {@inheritDoc}
     */
    public function getReplyToAddresses(int $uid): array
    {
        $message = $this->getMessageFromId($uid);
        return $this->getAddressesFromHeader($message, 'Reply-To');
    }

    /**
     * Extracts email addresses from the header
     * @param Mail\Storage\Message $message
     * @param string $addressType Header address type like `To`, `From`, `CC`, `BCC`
     * @return array
     */
    public function getAddressesFromHeader(Mail\Storage\Message $message, string $addressType) : array
    {
        if (!isset($message->$addressType)) {
            return [];
        }
        $header = $message->getHeader($addressType);
        $addresses = [];
        $list = $header->getAddressList();
        foreach ($list as $address) {
            $addresses[] = $address->getEmail();
        }
        return $addresses;
    }

    /**
     * Gets the Message-Id header as a string if it exists
     * @param int $uid
     * @return string
     */
    public function getMessageId(int $uid)
    {
        return $this->getHeaderAsString($uid, 'Message-Id');
    }

    /**
     * Gets Date header as a string if it exists
     * @param int $uid
     * @return string
     */
    public function getDate(int $uid)
    {
        return $this->getHeaderAsString($uid, 'Date');
    }

    /**
     * Delete a message on the server
     * @param int $uid
     */
    public function deleteMessage(int $uid)
    {
        $storage = $this->getStorage();
        $storage->removeMessage($uid);
    }

    /**
     * Gets a storage object
     * @return Mail\Storage\Imap
     */
    private function getStorage()
    {
        if ($this->storage) {
            return $this->storage;
        }
        return $this->storage = new Mail\Storage\Imap($this->client);
    }

    /**
     * Converts flag names to Laminas expected constants
     * @param $flag
     * @return mixed
     */
    private function getNormalizedFlagName($flag)
    {
        $flag = strtolower($flag);
        // hardcoded flags from Laminas\Mail\Storage
        $knownFlags = [
            'passed' => Mail\Storage::FLAG_PASSED,
            'answered' => Mail\Storage::FLAG_ANSWERED,
            'seen' => Mail\Storage::FLAG_SEEN,
            'unseen' => Mail\Storage::FLAG_UNSEEN,
            'deleted' => Mail\Storage::FLAG_DELETED,
            'draft' => Mail\Storage::FLAG_DRAFT,
            'flagged' => Mail\Storage::FLAG_FLAGGED,
        ];

        return $knownFlags[$flag];
    }
}
