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

require_once 'vendor/PHPMailer/PHPMailerAutoload.php';

class SMTPProxy extends SMTP
{
    /**
     * {@inheritDoc}
     */
    public function connect($host, $port = null, $timeout = 30, $options = array())
    {
        $result = parent::connect($host, $port, $timeout, $options);
        $this->handleError();

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * No error is handled if the NTLM client fails. Trying to do so would cause each error to be double-handled due to
     * the way {@link SMTPProxy::sendCommand()} is used. This is an edge case anyway.
     */
    public function authenticate(
        $username,
        $password,
        $authtype = 'LOGIN',
        $realm = '',
        $workstation = ''
    ) {
        // check if the resource is valid
        if (!is_resource($this->smtp_conn)) {
            $this->error = array('error' => 'Not a valid SMTP resource supplied');
            $this->handleError();

            return false;
        }

        return parent::authenticate($username, $password, $authtype, $realm, $workstation);
    }

    /**
     * {@inheritDoc}
     */
    public function turn()
    {
        $result = parent::turn();
        $this->handleError();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function sendCommand($command, $commandstring, $expect)
    {
        $result = parent::sendCommand($command, $commandstring, $expect);

        $this->handleError();

        return $result;
    }

    /**
     * Logs the error if one exists.
     */
    protected function handleError()
    {
        if (empty($this->error)) {
            return;
        }

        $message = array('SMTP ->');
        $level = 'warn';

        if (is_array($this->error)) {
            if (array_key_exists('error', $this->error)) {
                $message[] = "ERROR: {$this->error['error']}.";
            }

            $hasErrno = array_key_exists('errno', $this->error);
            $hasSmtpCode = array_key_exists('smtp_code', $this->error);

            if ($hasErrno || $hasSmtpCode) {
                // the presence of 'errno' or 'smtp_code' keys seems to indicate that a more serious error occurred
                // it was likely a failure when attempting to talk with an SMTP server
                $level = 'fatal';
            }

            if ($hasErrno) {
                $message[] = "Code: {$this->error['errno']}";
            } elseif ($hasSmtpCode) {
                $message[] = "Code: {$this->error['smtp_code']}";
            }

            if (array_key_exists('errstr', $this->error)) {
                $message[] = "Reply: {$this->error['errstr']}";
            } elseif (array_key_exists('detail', $this->error)) {
                $message[] = "Reply: {$this->error['detail']}";
            } elseif (array_key_exists('smtp_msg', $this->error)) {
                // kept around for legacy support
                // PHPMailer no longer uses 'smtp_msg'; 'detail' is used instead
                $message[] = "Reply: {$this->error['smtp_msg']}";
            }
        } else {
            $message[] = "ERROR: {$this->error}";
        }

        $GLOBALS['log']->$level(implode(' ', $message));
    }
}
