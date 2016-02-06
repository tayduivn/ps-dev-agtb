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

/* Internal Module Imports */


class PHPMailerProxy extends PHPMailer
{
    /**
     * {@inheritDoc}
     */
    public $AllowEmpty = true;

    /**
     * {@inheritDoc}
     *
     * Uses PHPMailer with exceptions.
     */
    public function __construct($exceptions = false)
    {
        parent::__construct(true);
        $this->Timeout = SugarConfig::getInstance()->get('email_mailer_timeout', 10);
    }

    /**
     * {@inheritDoc}
     *
     * @return SMTPProxy
     */
    public function getSMTPInstance()
    {
        if (!($this->smtp instanceof SMTPProxy)) {
            $this->smtp = new SMTPProxy();
        }

        return $this->smtp;
    }

    /**
     * {@inheritdoc}
     *
     * SugarCRM cleans values that appear as HTML in certain cases before inserting that data into the database. When
     * PHPMailer generates a Message-ID header, the string may begin with a "<", followed by an alphabetic
     * character, which will cause the Message-ID to be parsed as an invalid HTML tag by HTMLPurifier. To combat this,
     * the unix timestamp is prefixed to the Message-ID to guarantee that the Message-ID will begin with "<", followed
     * by an integer, which HTMLPurifier will correctly ignore as a non-threatening tag. This allows the Message-ID to
     * be saved to the database whenever appropriate, without risk of losing the value.
     *
     * Besides prefixing the unix timestamp, the rest of the Message-ID is generated exactly as PHPMailer does it. The
     * format of the Message-ID is <unix_timestamp.unique_id@server_hostname>. If the Message-ID is already provided,
     * then it will not be generated.
     */
    public function createHeader()
    {
        $time = time();

        if (empty($this->MessageID)) {
            $this->MessageID = sprintf('<%s.%s@%s>', $time, md5(uniqid($time)), $this->serverHostname());
        }

        return parent::createHeader();
    }

    /**
     * {@inheritDoc}
     */
    protected function setError($msg)
    {
        parent::setError($msg);

        $class = get_class($this);
        $GLOBALS['log']->fatal("{$class} encountered an error: {$this->ErrorInfo}");
    }
}
