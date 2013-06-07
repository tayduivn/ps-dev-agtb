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

require_once('clients/base/api/ModuleApi.php');
require_once('modules/Emails/MailRecord.php');
require_once('modules/Emails/EmailRecipientsService.php');

class MailApi extends ModuleApi
{
    public static $fields = array(
        "email_config"  => '',
        "to_addresses"  => array(),
        "cc_addresses"  => array(),
        "bcc_addresses" => array(),
        "attachments"   => array(),
        "documents"     => array(),
        "teams"         => array(),
        "related"       => array(),
        "subject"       => '',
        "html_body"     => '',
        "text_body"     => '',
        "status"        => "",
    );

    private $emailRecipientsService;

    public function registerApiRest()
    {
        $api = array(
            'listMail'        => array(
                'reqType'   => 'GET',
                'path'      => array('Mail'),
                'pathVars'  => array(''),
                'method'    => 'notSupported',
                'shortHelp' => 'List Mail Items',
                'longHelp'  => 'include/api/html/modules/Emails/MailApi.html#listMail',
            ),
            'retrieveMail'    => array(
                'reqType'   => 'GET',
                'path'      => array('Mail', '?'),
                'pathVars'  => array('', 'email_id'),
                'method'    => 'notSupported',
                'shortHelp' => 'Retrieve Mail Item',
                'longHelp'  => 'include/api/html/modules/Emails/MailApi.html#retrieveMail',
            ),
            'deleteMail'      => array(
                'reqType'   => 'DELETE',
                'path'      => array('Mail', '?'),
                'pathVars'  => array('', 'email_id'),
                'method'    => 'notSupported',
                'shortHelp' => 'Delete Mail Item',
                'longHelp'  => 'include/api/html/modules/Emails/MailApi.html#deleteMail',
            ),
            'updateMail'      => array(
                'reqType'   => 'PUT',
                'path'      => array('Mail', '?'),
                'pathVars'  => array('', 'email_id'),
                'method'    => 'notSupported', // 'updateMail',
                'shortHelp' => 'Update Mail Item',
                'longHelp'  => 'include/api/html/modules/Emails/MailApi.html#updateMail',
            ),
            'createMail'      => array(
                'reqType'   => 'POST',
                'path'      => array('Mail'),
                'pathVars'  => array(''),
                'method'    => 'createMail',
                'shortHelp' => 'Create Mail Item',
                'longHelp'  => 'include/api/html/modules/Emails/MailApi.html#createMail',
            ),
            'recipientLookup' => array(
                'reqType'   => 'POST',
                'path'      => array('Mail', 'recipient', 'lookup'),
                'pathVars'  => array(''),
                'method'    => 'recipientLookup',
                'shortHelp' => 'Lookup Email Recipient Info',
                'longHelp'  => 'include/api/html/modules/Emails/MailApi.html#recipientLookup',
            ),
            'listRecipients'  => array(
                'reqType'   => 'GET',
                'path'      => array('Mail', 'recipient', 'find'),
                'pathVars'  => array(''),
                'method'    => 'findRecipients',
                'shortHelp' => 'Search For Email Recipients',
                'longHelp'  => 'include/api/html/modules/Emails/MailApi.html#findRecipients',
            ),
        );

        return $api;
    }

    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function notSupported($api, $args)
    {
        throw new SugarApiExceptionNotFound();
    }

    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function createMail($api, $args)
    {
        return $this->handleMail($api, $args);
    }

    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function updateMail($api, $args)
    {
        $email = new Email();

        if (isset($args['email_id']) && !empty($args['email_id'])) {
            if ((!$email->retrieve($args['email_id'])) || ($email->id != $args['email_id'])) {
                throw new SugarApiExceptionMissingParameter();
            }

            if ($email->status != 'draft') {
                throw new SugarApiExceptionRequestMethodFailure();
            }
        } else {
            throw new SugarApiExceptionInvalidParameter();
        }

        return $this->handleMail($api, $args);
    }

    protected function handleMail($api, $args)
    {
        $result = array();

        foreach (self::$fields AS $k => $v) {
            if (!isset($args[$k])) {
                $args[$k] = $v;
            }
        }

        $mailRecord = $this->initMailRecord($args);

        if ($args["status"] == "ready") {
            if (empty($args["email_config"])) {
                throw new SugarApiExceptionRequestMethodFailure("No mail configuration specified.");
            }

            $result = $mailRecord->send();
        } elseif ($args["status"] == "draft") {
            $result = $mailRecord->saveAsDraft();
        } else {
            if (isset($GLOBALS["log"])) {
                $GLOBALS["log"]->error(
                    "MailApi: Request Failed - Invalid Request - Property=Status : '{$args["status"]}'"
                );
            }

            throw new SugarApiExceptionRequestMethodFailure("Invalid Status Property");
        }

        if (!isset($result['SUCCESS']) || !($result['SUCCESS'])) {
            $eMessage = isset($result['ERROR_MESSAGE']) ? $result['ERROR_MESSAGE'] : 'Unknown Request Failure';
            $eData    = isset($result['ERROR_DATA']) ? $result['ERROR_DATA'] : '';

            if (isset($GLOBALS["log"])) {
                $GLOBALS["log"]->error("MailApi: Request Failed - Message: {$eMessage}  Data: {$eData}");
            }

            throw new SugarApiExceptionRequestMethodFailure($eMessage);
        }

        if (isset($result["EMAIL"])) {
            $email           = $result["EMAIL"];
            $xmail           = clone $email;
            $result["EMAIL"] = $xmail->toArray();
        }

        return $result;
    }

    /**
     * This endpoint accepts an array of one or more recipients and tries to resolve unsupplied arguments.
     * EmailRecipientsService::lookup contains the lookup and resolution rules.
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function recipientLookup($api, $args)
    {
        $recipients = $args;
        unset($recipients['__sugar_url']);

        $emailRecipientsService = $this->getEmailRecipientsService();

        $result = array();
        foreach ($recipients as $recipient) {
            $result[] = $emailRecipientsService->lookup($recipient);
        }

        return $result;
    }

    /**
     * Arguments:
     *    q           - search string
     *    module_list -  one of the keys from $modules
     *    order_by    -  columns to sort by (one or more of $sortableColumns) with direction
     *                   ex.: name:asc,id:desc (will sort by last_name ASC and then id DESC)
     *    offset      -  offset of first record to return
     *    max_num     -  maximum records to return
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function findRecipients($api, $args) {
        ini_set("max_execution_time", 300);
        $term    = (isset($args["q"])) ? trim($args["q"]) : "";
        $offset  = 0;
        $limit   = (!empty($args["max_num"])) ? (int)$args["max_num"] : 20;
        $orderBy = array();

        if (!empty($args["offset"])) {
            if ($args["offset"] === "end") {
                $offset = "end";
            } else {
                $offset = (int)$args["offset"];
            }
        }

        $modules = array(
            "users"     => "users",
            "accounts"  => "accounts",
            "contacts"  => "contacts",
            "leads"     => "leads",
            "prospects" => "prospects",
            "all"       => "LBL_DROPDOWN_LIST_ALL",
        );
        $module  = $modules["all"];

        if (!empty($args["module_list"])) {
            $moduleList = strtolower($args["module_list"]);

            if (array_key_exists($moduleList, $modules)) {
                $module = $modules[$moduleList];
            }
        }

        if (!empty($args["order_by"])) {
            $orderBys = explode(",", $args["order_by"]);

            foreach ($orderBys as $sortBy) {
                $column    = $sortBy;
                $direction = "ASC";

                if (strpos($sortBy, ":")) {
                    // it has a :, it's specifying ASC / DESC
                    list($column, $direction) = explode(":", $sortBy);

                    if (strtolower($direction) == "desc") {
                        $direction = "DESC";
                    } else {
                        $direction = "ASC";
                    }
                }

                // only add column once to the order-by clause
                if (empty($orderBy[$column])) {
                    $orderBy[$column] = $direction;
                }
            }
        }

        $records    = array();
        $nextOffset = -1;

        if ($offset !== "end") {
            $emailRecipientsService = $this->getEmailRecipientsService();
            $totalRecords           = $emailRecipientsService->findCount($term, $module);
            $records                = $emailRecipientsService->find($term, $module, $orderBy, $limit, $offset);
            $trueOffset             = $offset + $limit;

            if ($trueOffset < $totalRecords) {
                $nextOffset = $trueOffset;
            }
        }

        return array(
            "next_offset" => $nextOffset,
            "records"     => $records,
        );
    }

    protected function initMailRecord($args)
    {
        $mailRecord               = new MailRecord();
        $mailRecord->mailConfig   = $args["email_config"];
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

        return $mailRecord;
    }

    protected function getEmailRecipientsService()
    {
        if (!($this->emailRecipientsService instanceof EmailRecipientsService)) {
            $this->emailRecipientsService = new EmailRecipientsService;
        }

        return $this->emailRecipientsService;
    }
}
