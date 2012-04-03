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
 * Note:
 *
 * This file is much like the modules.php file where it is used to loaded known
 * objects by the RestFactory.
 */

/*
 * Setup the objects by name first.
 */
$restObjectList = array(
    "login" => array(),
    "logout" => array(),
    "metadata" => array(),
    "serverinfo" => array(),
    "listobjects" => array(),
    "objects" => array(),
    "labels" => array(),
);

/*
 * Setup the objects source file.
 */
$restObjectList["login"] = "internalObjects/login.php";
$restObjectList["logout"] = "internalObjects/logout.php";
$restObjectList["metadata"] = "internalObjects/metadata.php";
$restObjectList["serverinfo"] = "internalObjects/serverinfo.php";
$restObjectList["listobjects"] = "internalObjects/listobjects.php";
$restObjectList["objects"] = "internalObjects/objects.php";
$restObjectList["labels"] = "internalObjects/labels.php";


/*
 * setup some defines that we will use later in life.
 */
define("HTTP_OPTIONS",1001);
define("HTTP_GET", 1002);
define("HTTP_HEAD", 1003);
define("HTTP_POST", 1004);
define("HTTP_PUT",1005);
define("HTTP_DELETE", 1006);
define("HTTP_TRACE",1007);
define("HTTP_CONNECT", 1008);
define("HTTP_PATCH", 1009);
define("REST_OBJECT_INDEX", 0);
define("REST_OBJECT_RESOURCE_INDEX", 1);
