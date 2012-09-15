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
    public $description = "An unknown exception happened.";
    public $errorLabel = 'unknown_exception';
    public $userMessage = null;

    function __construct($description = null, $httpCode = 0, $errorLabel = null)
    {
        if (!empty($description)) {
            $this->description = $description;
        }

        if (!empty($errorLabel)) {
            $this->errorLabel = $errorLabel;
        }
        
        if ($httpCode != 0) {
            $this->httpCode = $httpCode;
        }

        parent::__construct($this->description);
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getErrorLabel()
    {
        return $this->errorLabel;
    }

    /**
     * Used to load user messages that get returned with some Sugar REST API errors.
     * The difference between description and this message is that the message
     * is translated and intended to be displayed to end users.
     * @param array @args OPTIONAL array of arguments to replace in user message
     * @return String translated string if message exists, NULL if no user message available
     */
    public function getUserMessage($args = array()){
        // Load app list strings if they haven't been already
        global $app_list_strings;
        if(!isset($app_list_strings)){
            $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        }
        // Check the current error label against the REST API DOM
        $restApiMessages = $app_list_strings["rest_api_error_message"];
        if(isset($restApiMessages[$this->errorLabel])){
            return string_format($restApiMessages[$this->errorLabel],$args);
        }
        return null;
    }

}
class SugarApiExceptionError extends SugarApiException 
{ 
    public $httpCode = 500; 
    public $errorLabel = 'fatal_error';
    public $description = "A fatal error happened."; 
}
class SugarApiExceptionNeedLogin extends SugarApiException 
{ 
    public $httpCode = 401; 
    public $errorLabel = 'need_login';
    public $description = "The user needs to be logged in to perform this action";
}
class SugarApiExceptionNotAuthorized extends SugarApiException 
{ 
    public $httpCode = 403; 
    public $errorLabel = 'not_authorized';
    public $description = "This action is not authorized for the current user.";
}
class SugarApiExceptionCreateNotAuthorized extends SugarApiException
{
    public $httpCode = 403;
    public $errorLabel = 'create_not_authorized';
    public $description = 'No access to create new records';
    private $moduleName = null;

    /**
     * @param String $moduleName Name of module that user lacks create access
     */
    function __construct($moduleName = null)
    {
        $this->description = 'No access to create new records for module: ' . $moduleName;
        $this->moduleName = $moduleName;
        parent::__construct($this->description);
    }

    /**
     * Overloading so we can return error message with module name
     * @return String message with module name
     */
    public function getUserMessage(){
        $mod_strings = return_module_language($GLOBALS['current_language'], $this->moduleName);
        return parent::getUserMessage(array($mod_strings['LBL_MODULE_NAME']));
    }
}

class SugarApiExceptionPortalNotConfigured extends SugarApiException
{
    public $httpCode = 403;
    public $errorLabel = 'portal_not_configured';
    public $description = 'No portal api user or portal not enabled.';
}

class SugarApiExceptionNoMethod extends SugarApiException 
{
    public $httpCode = 404;
    public $errorLabel = 'no_method';
    public $description = "Could not find a method for this path.";
}
class SugarApiExceptionNotFound extends SugarApiException
{
    public $httpCode = 404;
    public $errorLabel = 'not_found';
    public $description = "Could not find a handler for this path.";
}
class SugarApiExceptionMissingParameter extends SugarApiException
{
    public $httpCode = 412;
    public $errorLabel = 'missing_parameter';
    public $description = "A required parameter for this request is missing.";
}
class SugarApiExceptionInvalidParameter extends SugarApiException
{
    public $httpCode = 412;
    public $errorLabel = 'invalid_parameter';
    public $description = "A parameter for this request is invalid.";
}
class SugarApiExceptionRequestMethodFailure extends SugarApiException
{
    public $httpCode = 412;
    public $errorLabel = 'request_failure';
    public $description = "The requested method failed.";
}
class SugarApiExceptionRequestTooLarge extends SugarApiException
{
    public $httpCode = 413;
    public $errorLabel = 'request_too_large';
    public $description = "The request is too large to process.";
}

