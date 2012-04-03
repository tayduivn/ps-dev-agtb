<?php
if (!defined('sugarEntry')) define('sugarEntry', true);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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

include_once("include/rest/RestObjectInterface.php");
include_once("include/modules.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
include_once("include/MetaDataManager/MetaDataManager.php");
include_once("include/rest/SoapHelperWebService.php");
include_once("include/rest/SugarWebServiceImpl.php");

class ListObjects extends RestObject implements IRestObject {

    function __construct() {
        parent::__construct();

        $this->verbID = $this->verbToId();
        $this->getAuth();
    }

    public function execute() {
        $result = null;

        switch ($this->verbID) {
            case HTTP_GET:
                $result = $this->handleGet();
                break;
            default:
                break;
        }

        return $result;
    }

    private function handleGet() {
        $result = null;
        global $current_user;
        $auth = $this->getAuth();
        $this->isValidToken($auth);

        $obj = new SugarWebServiceImpl();
        $result = $obj->get_available_modules($auth);

        if (!array_key_exists("modules", $result)) {
            $err = new RestError();
            $err->ReportError(501);
            exit;
        }

        $result = array_values($result["modules"]);
        $result = json_encode($result);

        print $result;
    }
}