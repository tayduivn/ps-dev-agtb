<?php
if(!defined("sugarEntry") || !sugarEntry) die("Not A Valid Entry Point");

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

require_once("clients/base/api/ModuleApi.php");
require_once("modules/UserSignatures/UserSignature.php");

class SignaturesApi extends ModuleApi
{
    protected $controller = "Signatures";

    public function __construct() {}

    /**
     * @return array
     */
    public function registerApiRest()
    {
        $api = array(
            "listSignatures"    => array(
                "reqType"   => "GET",
                "path"      => array($this->controller),
                "pathVars"  => array($this->controller),
                "method"    => "listSignatures",
                "shortHelp" => "Retrieve all signatures for the current user",
                "longHelp"  => "",
            ),
            "retrieveSignature" => array(
                "reqType"   => "GET",
                "path"      => array($this->controller, "?"),
                "pathVars"  => array($this->controller, "signatureId"),
                "method"    => "retrieveSignature",
                "shortHelp" => "Retrieve a particular signature for the current user",
                "longHelp"  => "",
            ),

        );

        return $api;
    }

    /**
     * Retrieve the current user's signature identified by the signatureId argument.
     *
     * @param $api
     * @param $args
     *
     * @return array
     * @throws SugarApiExceptionMissingParameter
     * @throws SugarApiExceptionNotFound
     */
    public function retrieveSignature($api, $args)
    {
        $this->requireArgs($args, array("signatureId"));

        $signatureId = $args["signatureId"];
        $signature   = $GLOBALS["current_user"]->getSignature($signatureId);

        if ($signature === false) {
            throw new SugarApiExceptionNotFound("Could not find parent record {$signatureId}");
        }

        return $signature;
    }

    /**
     * Retrieve all of the current user's signatures with the default signature identified.
     *
     * The limit and offset behavior is intended to mimic the same behavior found in the FilterApi. Once signatures
     * becomes its own module, the FilterApi can be used to fetch signatures. Until then, it is desired to have this
     * endpoint behave similarly to FilterApi::filterList.
     *
     * @param $api
     * @param $args
     *
     * @return array
     */
    public function listSignatures($api, $args)
    {
        $limit  = 20;
        $offset = 0;

        if (!empty($args["max_num"])) {
            $limit = (int)$args["max_num"];
        }

        if (!empty($args["offset"])) {
            if ($args["offset"] === "end") {
                $offset = "end";
            } else {
                $offset = (int)$args["offset"];
            }
        }

        $records          = array();
        $signatures       = $GLOBALS["current_user"]->getSignaturesArray();
        $defaultSignature = $GLOBALS["current_user"]->getDefaultSignature();

        // if offset is "end", then skip all records
        $numberOfRecordsToSkip    = ($offset === "end") ? count($signatures) : $offset;
        $numberOfRecordsCollected = 0;

        foreach ($signatures as $id => $signature) {
            if (!empty($id)) {
                if ($numberOfRecordsToSkip > 0) {
                    $numberOfRecordsToSkip--;
                } else {
                    $records[] = array(
                        "id"      => $id,
                        "name"    => $signature["name"],
                        "default" => (!empty($defaultSignature) && $id == $defaultSignature["id"]),
                    );

                    $numberOfRecordsCollected = count($records);

                    if ($numberOfRecordsCollected == $limit) {
                        break;
                    }
                }
            }
        }

        $nextOffset = -1;

        // there are no more records if:
        // - offset is "end"
        // - no records were collected
        // - collected record count is less than the limit
        if ($offset !== "end" && $numberOfRecordsCollected > 0 && $numberOfRecordsCollected == $limit) {
            $nextOffset = $offset + $limit;
        }

        return array(
            "next_offset" => $nextOffset,
            "records"     => $records,
        );
    }
}
