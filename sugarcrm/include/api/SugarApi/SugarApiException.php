<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

class SugarApiException extends Exception
{ 
    public $httpCode = 400;
    public $errorLabel = 'unknown_exception';

    function __construct($userMessage = null, $httpCode = 0, $errorLabel = null)
    {
        if(empty($userMessage)) {
            // If no message set, load default user message
            global $app_list_strings;
            if(!isset($app_list_strings)){
                $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
            }
            $restApiMessages = $app_list_strings["rest_api_default_messages"];
            // Default messages are keyed off error label
            if(isset($restApiMessages[$this->errorLabel])){
                $userMessage = $restApiMessages[$this->errorLabel];
            }
        }

        if (!empty($errorLabel)) {
            $this->errorLabel = $errorLabel;
        }
        
        if ($httpCode != 0) {
            $this->httpCode = $httpCode;
        }

        parent::__construct($userMessage);
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Each Sugar API exception should have a unique label that clients can use to identify which
     * Sugar API exception was thrown.
     *
     * @return null|string Unique error label
     */
    public function getErrorLabel()
    {
        return $this->errorLabel;
    }

    /**
     * Set the error message that gets returned to clients.  Error message should be suitable for display to end users.
     * @param String $message
     */
    public function setMessage($message){
        $this->message = $message;
    }

}
class SugarApiExceptionError extends SugarApiException 
{ 
    public $httpCode = 500; 
    public $errorLabel = 'fatal_error';
}
class SugarApiExceptionNeedLogin extends SugarApiException 
{ 
    public $httpCode = 401; 
    public $errorLabel = 'need_login';
}
class SugarApiExceptionNotAuthorized extends SugarApiException 
{ 
    public $httpCode = 403; 
    public $errorLabel = 'not_authorized';
}

class SugarApiExceptionPortalNotConfigured extends SugarApiException
{
    public $httpCode = 403;
    public $errorLabel = 'portal_not_configured';
}

class SugarApiExceptionNoMethod extends SugarApiException 
{
    public $httpCode = 404;
    public $errorLabel = 'no_method';
}
class SugarApiExceptionNotFound extends SugarApiException
{
    public $httpCode = 404;
    public $errorLabel = 'not_found';
}
class SugarApiExceptionMissingParameter extends SugarApiException
{
    public $httpCode = 412;
    public $errorLabel = 'missing_parameter';
}
class SugarApiExceptionInvalidParameter extends SugarApiException
{
    public $httpCode = 412;
    public $errorLabel = 'invalid_parameter';
}
class SugarApiExceptionRequestMethodFailure extends SugarApiException
{
    public $httpCode = 412;
    public $errorLabel = 'request_failure';
}
class SugarApiExceptionRequestTooLarge extends SugarApiException
{
    public $httpCode = 413;
    public $errorLabel = 'request_too_large';
}

