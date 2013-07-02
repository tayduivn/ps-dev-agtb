<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
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

class MailerException extends Exception
{
    const ResourceNotFound              = 1;
    const InvalidConfiguration          = 2;
    const InvalidHeader                 = 3;
    const InvalidEmailAddress           = 4;
    const FailedToSend                  = 5;
    const FailedToConnectToRemoteServer = 6;
    const FailedToTransferHeaders       = 7;
    const InvalidMessageBody            = 8;
    const InvalidAttachment             = 9;
    const InvalidMailer                 = 10;

    static protected $errorMessageMappings = array(
        self::ResourceNotFound              => 'LBL_INTERNAL_ERROR',
        self::InvalidConfiguration          => 'LBL_INVALID_CONFIGURATION',
        self::InvalidHeader                 => 'LBL_INVALID_HEADER',
        self::InvalidEmailAddress           => 'LBL_INVALID_EMAIL',
        self::FailedToSend                  => 'LBL_INTERNAL_ERROR',
        self::FailedToConnectToRemoteServer => 'LBL_FAILED_TO_CONNECT',
        self::FailedToTransferHeaders       => 'LBL_INTERNAL_ERROR',
        self::InvalidAttachment             => 'LBL_INVALID_ATTACHMENT',
        self::InvalidMailer                 => 'LBL_INTERNAL_ERROR',
    );

    public function getLogMessage() {
        return "MailerException - @(" . basename($this->getFile()) . ":" .  $this->getLine() . " [" . $this->getCode() . "]" . ") - " . $this->getMessage();
    }
    public function getTraceMessage() {
        return "MailerException: (Trace)\n" . $this->getTraceAsString();
    }
    public function getUserFriendlyMessage() {
        $moduleName = 'Emails';
        if (isset(self::$errorMessageMappings[$this->getCode()])) {
            $exception_code = self::$errorMessageMappings[$this->getCode()];
        }
        if (empty($exception_code)) {
            $exception_code = 'LBL_INTERNAL_ERROR'; //use generic message if a user-friendly version is not available
        }
        return translate($exception_code, $moduleName);
    }
}
