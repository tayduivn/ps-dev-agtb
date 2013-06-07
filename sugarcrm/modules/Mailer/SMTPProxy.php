<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/* Third-Party Library Imports */

/**
 * Required to establish the SMTP connection prior to PHPMailer's send for error handling purposes.
 */
require_once "vendor/PHPMailer/class.smtp.php";

class SMTPProxy extends SMTP
{
    public function Connect($host, $port = 0, $timeout = 30, $options = array())
    {
        $result = parent::Connect($host, $port, $timeout, $options);
        $this->handleError();

        return $result;
    }

    public function StartTLS()
    {
        $result = parent::StartTLS();
        $this->handleError();

        return $result;
    }

    public function Authenticate($username, $password, $authtype='LOGIN', $realm='', $workstation='')
    {
        $result = false;

        // check if the resource is valid
        if (!is_resource($this->smtp_conn)) {
            $this->error = array("error" => "Not a valid SMTP resource supplied");
        } else {
            $result = parent::Authenticate($username, $password, $authtype, $realm, $workstation);
        }

        $this->handleError();

        return $result;
    }

    public function Data($msg_data)
    {
        $result = parent::Data($msg_data);
        $this->handleError();

        return $result;
    }

    public function Hello($host = '')
    {
        $result = parent::Hello($host);
        $this->handleError();

        return $result;
    }

    public function Mail($from)
    {
        $result = parent::Mail($from);
        $this->handleError();

        return $result;
    }

    public function Quit($close_on_error = true)
    {
        $result = parent::Quit($close_on_error);
        $this->handleError();

        return $result;
    }

    public function Recipient($to)
    {
        $result = parent::Recipient($to);
        $this->handleError();

        return $result;
    }

    public function Reset()
    {
        $result = parent::Reset();
        $this->handleError();

        return $result;
    }

    public function SendAndMail($from)
    {
        $result = parent::SendAndMail($from);
        $this->handleError();

        return $result;
    }

    public function Turn()
    {
        $result = parent::Turn();
        $this->handleError();

        return $result;
    }

    public function client_send($data)
    {
        $result = parent::client_send($data);
        $this->handleError();

        return $result;
    }

    protected function handleError()
    {
        if (!is_null($this->error)) {
            $message = array("SMTP ->");
            $level   = "warn";

            if (is_array($this->error)) {
                if (array_key_exists("error", $this->error)) {
                    $message[] = "ERROR: {$this->error["error"]}.";
                }

                $hasErrno    = array_key_exists("errno", $this->error);
                $hasSmtpCode = array_key_exists("smtp_code", $this->error);

                if ($hasErrno || $hasSmtpCode) {
                    // the presence of "errno" or "smtp_code" keys seems to indicate that a more serious error occurred
                    // it was likely a failure when attempting to talk with an SMTP server
                    $level = "fatal";
                }

                if ($hasErrno) {
                    $message[] = "Code: {$this->error["errno"]}";
                } elseif ($hasSmtpCode) {
                    $message[] = "Code: {$this->error["smtp_code"]}";
                }

                if (array_key_exists("errstr", $this->error)) {
                    $message[] = "Reply: {$this->error["errstr"]}";
                } elseif (array_key_exists("smtp_msg", $this->error)) {
                    $message[] = "Reply: {$this->error["smtp_msg"]}";
                }
            } else {
                $message[] = "ERROR: {$this->error}";
            }

            $GLOBALS["log"]->$level(implode(" ", $message));
        }
    }
}
