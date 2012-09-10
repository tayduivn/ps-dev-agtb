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