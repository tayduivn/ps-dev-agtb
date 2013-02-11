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

require_once('clients/base/api/ModuleApi.php');

class MailRecipientApi extends ModuleApi
{
    public static $modules = array(
        "users"      =>  "users",
        "accounts"   =>  "accounts",
        "contacts"   =>  "contacts",
        "leads"      =>  "leads",
        "prospects"  =>  "prospects",
        "all"        =>  "LBL_DROPDOWN_LIST_ALL",
    );

    public static $sortableColumns = array(
        // column_from_$args => $args_column_mapped_to_real_database_column
        'id'    => 'id',
        'email' => 'email_address',
        'name'  => 'last_name',
    );

    public function registerApiRest() {
        $api = array(
            'listRecipients'  => array(
                'reqType'   => 'GET',
                'path'      => array('MailRecipient'),
                'pathVars'  => array(''),
                'method'    => 'listRecipients',
                'shortHelp' => 'Search For Mail Recipients',
                'longHelp'  => '',
            ),
        );

        return $api;
    }

    /**
     * Arguments:
     *    q           - search string
     *    module_list -  one of the keys from self::$modules
     *    order_by    -  columns to sort by (one or more of self::$sortableColumns) with direction
     *                   ex.: name:asc,id:desc (will sort by last_name ASC and then id DESC)
     *    offset      -  offset of first record to return
     *    max_num     -  maximum records to return
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function listRecipients($api, $args) {
        global $locale;
        ini_set("max_execution_time", 300);

        $options = array(
            "limit"          => 20, //TODO: should this come from a config?
            "offset"         => 0,
            "moduleList"     => self::$modules["all"],
            "orderBy"        => "",
        );

        $wheres = array();

        if (isset($args["q"])) {
            $q                       = trim($args["q"]);
            $wheres["first_name"]    = $q;
            $wheres["last_name"]     = $q;
            $wheres["email_address"] = $q;
        }

        if (!empty($args["max_num"])) {
            $options["limit"] = (int)$args["max_num"];
        }

        if (!empty($args["offset"])) {
            if ($args["offset"] === "end") {
                $options["offset"] = "end";
            } else {
                $options["offset"] = (int)$args["offset"];
            }
        }

        if (!empty($args["module_list"])) {
            $moduleList = strtolower($args["module_list"]);

            if (array_key_exists($moduleList, self::$modules)) {
                $options["moduleList"] = self::$modules[$moduleList];
            }
        }

        $email = new Email();
        $email->email2init();

        $inboundEmail        = new InboundEmail();
        $inboundEmail->email = $email;

        $totalRecords        = 0;
        $records             = array();
        $relatedEmailQueries = $email->et->getRelatedEmail($options["moduleList"], $wheres);

        if (!empty($relatedEmailQueries["countQuery"])) {
            $startTime = microtime(true);
            $result = $inboundEmail->db->query($relatedEmailQueries["countQuery"]);
            $runTime = microtime(true) - $startTime;
            $GLOBALS['log']->debug("*** MailRecipientSearch - Count(*): {$runTime} milliseconds\n");

            if ($row = $inboundEmail->db->fetchByAssoc($result)) {
                $totalRecords = (int)$row["c"];
            }
        }

        if (!empty($relatedEmailQueries["query"])) {
            $orderByData  = array();

            if (!empty($args["order_by"])) {
                $orderBys     = explode(",", $args["order_by"]);
                $orderByArray = array();

                foreach ($orderBys as $order) {
                    $column    = $order;
                    $direction = "ASC";

                    if (strpos($order, ":")) {
                        // it has a :, it's specifying ASC / DESC
                        list($column, $direction) = explode(":", $order);

                        if (strtolower($direction) == "desc") {
                            $direction = "DESC";
                        } else {
                            $direction = "ASC";
                        }
                    }

                    $column = $inboundEmail->db->getValidDBName($column);

                    // only allow for sorting on a predetermined set of columns
                    if (array_key_exists($column, self::$sortableColumns)) {
                        // the column name must be mapped to another name
                        $column = self::$sortableColumns[$column];

                        if (empty($orderByData[$column])) {
                            // only add column once to the order-by clause
                            $orderByData[$column] = ($direction == "ASC") ? true : false;
                            $orderByArray[]       = "{$column} {$direction}";
                        }
                    }
                }

                $options["orderBy"] = implode(",", $orderByArray);
            } else {
                $options["orderBy"] = "id DESC";
                $orderByData["id"]  = false;
            }

            if (!empty($options["orderBy"])) {
                $options["orderBy"] = " ORDER BY {$options["orderBy"]}";
            }

            $startTime = microtime(true);
            $sql       = "{$relatedEmailQueries["query"]} {$options["orderBy"]}";
            $result    = $inboundEmail->db->limitQuery($sql, $options["offset"], $options["limit"], true);
            $runTime   = microtime(true) - $startTime;
            $GLOBALS["log"]->debug("*** MailRecipientSearch - Results: {$runTime} milliseconds");

            while($row = $inboundEmail->db->fetchByAssoc($result)) {
                $records[] = array(
                    "id"     => $row["id"],
                    "module" => $row["module"],
                    "name"   => $locale->getLocaleFormattedName($row["first_name"], $row["last_name"]),
                    "email"  => $row["email_address"],
                );
            }
        }

        $nextOffset = -1;

        if ($options["offset"] !== "end") {
            $trueOffset = $options["offset"] + $options["limit"];

            if ($trueOffset < $totalRecords) {
                $nextOffset = $trueOffset;
            }
        }

        return array(
            "next_offset" => $nextOffset,
            "records"     => $records,
        );
    }
}
