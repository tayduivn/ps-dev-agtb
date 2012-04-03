<?php
if (!defined('sugarEntry')) define('sugarEntry', true);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * This is a class for creating HTTP error responses.
 *
 */
class RestError {

    private $errorData = null;

    /**
     * Class constructor.  Currently just building a list of error codes at runtime, might want
     * to move this to a static list of it ever gets too big to save on processor time.  Don't really
     * think that will ever be an issue though.
     */
    function __construct() {

        $this->errorData = array(
            400 => "",
            401 => "The session ID or OAuth token used has expired or is invalid.",
            403 => "The request has been refused. Verify that the logged-in user has appropriate permissions.",
            404 => "The requested resource could not be found. Check the URI for errors, and" .
                " verify that there are no sharing issues.",
            415 => "The server is refusing to service the request because the entity of the ".
                "request is in a format not supported by the requested resource for the requested method.",
            410 => "",
            501 => "Internal SugarCRM Error!"
        );
    }

    /**
     * This method creates an HTTP error from the passed in error code, and appends a message to the
     * error's body if the user sets $msg to something other then null.
     *
     * @param $code The http error code to use.
     * @param null $msg An extra message body to append to the default one.
     */
    public function ReportError($code, $msg = null) {
        header("HTTP/1.0 {$code}");
        print $this->errorData[$code];

        if ($msg != null) {
            print "\n\n{$msg}\n";
        }
    }
}

