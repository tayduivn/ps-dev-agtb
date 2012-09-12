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

require_once('include/api/ModuleApi.php');
//require_once('modules/Mailer/SimpleMailer.php');
require_once('modules/Emails/MailRecord.php');


class EmailsApi extends ModuleApi
{
    public static $fields = array (

        "to_addresses"      => null,
        "cc_addresses"      => null,
        "bcc_addresses"     => null,

        "attachments"       => null,
        "documents"         => null,
        "teams"             => null,
        "related"           => null,

        "subject"           => null,
        "html_body"         => null,
        "text_body"         => null,

        "status"            => "",
    );

    public function __construct() {}

    public function registerApiRest() {
        $api = array(

            /***/
            'listMail'     => array(
                'reqType'   => 'GET',
                'path'      => array('Emails'),
                'pathVars'  => array(''),
                'method'    => 'notSupported',
                'shortHelp' => 'List Mail Items',
                'longHelp'  => 'include/api/html/modules/Emails/EmailsApi.html#listMail',
            ),


            'retrieveMail' => array(
                'reqType'   => 'GET',
                'path'      => array('Emails', '?'),
                'pathVars'  => array('', 'email_id'),
                'method'    => 'notSupported',
                'shortHelp' => 'Retrieve Mail Item',
                'longHelp'  => 'include/api/html/modules/Emails/EmailsApi.html#retrieveMail',
            ),


            'deleteMail'     => array(
                'reqType'   => 'DELETE',
                'path'      => array('Emails', '?'),
                'pathVars'  => array('', 'email_id'),
                'method'    => 'notSupported',
                'shortHelp' => 'Delete Mail Item',
                'longHelp'  => 'include/api/html/modules/Emails/EmailsApi.html#updateMail',
            ),

            /***/

            'createMail'     => array(
                'reqType'   => 'POST',
                'path'      => array('Emails'),
                'pathVars'  => array(''),
                'method'    => 'createMail',
                'shortHelp' => 'Create Mail Item',
                'longHelp'  => 'include/api/html/modules/Emails/EmailsApi.html#createMail',
            ),

            'updateMail'     => array(
                'reqType'   => 'PUT',
                'path'      => array('Emails', '?'),
                'pathVars'  => array('', 'email_id'),
                'method'    => 'updateMail',
                'shortHelp' => 'Update Mail Item',
                'longHelp'  => 'include/api/html/modules/Emails/EmailsApi.html#updateMail',
            ),

        );

        return $api;
    }


    /**-----------
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
    -----***/



    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function createMail($api, $args) {

        $result = $this->handleMail($api, $args);

        return $result;
    }


    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function updateMail($api, $args) {
        global $current_user;
        $email = new Email();

        if(isset($args['email_id']) && !empty($args['email_id'])) {
            if (!$email->retrieve($args['email_id'])) {
                throw new SugarApiExceptionMissingParameter();
            }
            if ($email->status != 'draft') {
                throw new SugarApiExceptionRequestMethodFailure();
            }
        } else {
            throw new SugarApiExceptionInvalidParameter();
        }

        $result = $this->handleMail($api, $args);

        return $result;
    }


    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function retrieveMail($api, $args) {
        global $current_user;
        $email = new Email();

        if(isset($args['email_id']) && !empty($args['email_id'])) {
            if (!$email->retrieve($args['email_id'])) {
                throw new SugarApiExceptionMissingParameter();
            }
            if ($email->status != 'draft') {
                throw new SugarApiExceptionRequestMethodFailure();
            }
        } else {
            throw new SugarApiExceptionInvalidParameter();
        }

        $email->email2init();

        $result = array(
            "FUNCTION"   => "retrieveMail",
            "ARGS"       => $args,
            "EMAIL"      => $email->toArray(),
        );

        return $result;
    }


    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function notSupported($api, $args) {

        throw new SugarApiExceptionNotFound();

        // $result = array(
        //    "ERROR"   => "Function Not Supported",
        //    "ARGS"    => $args,
        // );
        // return $result;
    }



    protected function handleMail($api, $args) {
        global $current_user;

        foreach(self::$fields AS $k => $v) {
            if (!isset($args[$k])) {
                $args[$k] = $v;
            }
        }

        ob_start();

        $mailRecord = new MailRecord($current_user);

        $mailRecord->toAddresses  = $args["to_addresses"];
        $mailRecord->ccAddresses  = $args["cc_addresses"];
        $mailRecord->bccAddresses = $args["bcc_addresses"];

        $mailRecord->attachments  = $args["attachments"];
        $mailRecord->documents    = $args["documents"];
        $mailRecord->teams        = $args["teams"];
        $mailRecord->related      = $args["related"];

        $mailRecord->subject      = $args["subject"];
        $mailRecord->html_body    = $args["html_body"];
        $mailRecord->text_body    = $args["text_body"];

        if ($args["status"] == "ready") {
            $result = $mailRecord->send();
        }
        else if ($args["status"] == "draft") {
            $result = $mailRecord->saveAsDraft();
        }
        else {
            throw new SugarApiExceptionRequestMethodFailure("Invalid Status");
        }

        if (isset($result["EMAIL"])) {
            $email = $result["EMAIL"];
            $xmail = clone $email;
            $result["EMAIL"] = $xmail->toArray();
        }

        return $result;
    }

}