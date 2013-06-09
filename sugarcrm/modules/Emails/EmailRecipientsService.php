<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) decodesublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

class EmailRecipientsService
{
    protected $sugarEmail,
              $beanNames;

    public function __construct()
    {
        $this->sugarEmail = null;
        $this->beanNames  = null;
    }

    /**
     * Check if an email address is valid.
     *
     * @see PHPMailerProxy::ValidateAddress()
     * @param string $emailAddress
     * @return bool
     */
    public function isValidEmailAddress($emailAddress = "")
    {
        $isValid = false;

        if (!empty($emailAddress)) {
            // the best regex we have for validating email addresses is in PHPMailer, so let's just use that one
            require_once "modules/Mailer/PHPMailerProxy.php";
            $isValid = PHPMailerProxy::ValidateAddress($emailAddress);
        }

        return $isValid;
    }

    /**
     * Find the total number of recipients, in one or all modules, that match the search term.
     *
     * @param string $term
     * @param string $module
     * @return int
     */
    public function findCount($term = "", $module = "LBL_DROPDOWN_LIST_ALL")
    {
        $totalRecords = 0;
        $inboundEmail = $this->setUpForRelatedEmailQueries();
        $wheres       = array();

        if (!empty($term)) {
            $wheres["first_name"]    = $term;
            $wheres["last_name"]     = $term;
            $wheres["email_address"] = $term;
        }

        $relatedEmailQueries = $inboundEmail->email->et->getRelatedEmail($module, $wheres);

        if (!empty($relatedEmailQueries["countQuery"])) {
            $startTime = microtime(true);
            $result    = $inboundEmail->db->query($relatedEmailQueries["countQuery"]);
            $runTime   = microtime(true) - $startTime;
            $GLOBALS["log"]->debug("EmailRecipientsService::findCount took {$runTime} milliseconds");

            if ($row = $inboundEmail->db->fetchByAssoc($result)) {
                $totalRecords = (int)$row["c"];
            }
        }

        return $totalRecords;
    }

    /**
     * Find recipients, in one or all modules, that match the search term.
     *
     * @param string $term
     * @param string $module
     * @param array  $orderBy
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function find($term = "", $module = "LBL_DROPDOWN_LIST_ALL", $orderBy = array(), $limit = 0, $offset = 0)
    {
        $records      = array();
        $inboundEmail = $this->setUpForRelatedEmailQueries();
        $wheres       = array();

        if (!empty($term)) {
            $wheres["first_name"]    = $term;
            $wheres["last_name"]     = $term;
            $wheres["email_address"] = $term;
        }

        $relatedEmailQueries = $inboundEmail->email->et->getRelatedEmail($module, $wheres);

        if (!empty($relatedEmailQueries["query"])) {
            $sql             = $relatedEmailQueries["query"];
            $sortableColumns = array(
                // column_from_$args => $args_column_mapped_to_real_database_column
                "id"    => "id",
                "email" => "email_address",
                "name"  => "last_name",
            );
            $sort            = array();

            if (empty($orderBy)) {
                $orderBy["id"] = "DESC";
            }

            foreach ($orderBy as $column => $direction) {
                $column = $inboundEmail->db->getValidDBName($column);

                // only allow for sorting on a predetermined set of columns
                if (array_key_exists($column, $sortableColumns)) {
                    // the column name must be mapped to another name
                    $sort[] = "{$sortableColumns[$column]} {$direction}";
                }
            }

            if (!empty($sort)) {
                $sql .= " ORDER BY " . implode(",", $sort);
            }

            $result    = null;
            $startTime = microtime(true);

            if ($limit > 0) {
                $result = $inboundEmail->db->limitQuery($sql, $offset, $limit, true);
            } else {
                $result = $inboundEmail->db->query($sql, true);
            }

            $runTime = microtime(true) - $startTime;
            $GLOBALS["log"]->debug("EmailRecipientsService::find took {$runTime} milliseconds");

            while($row = $inboundEmail->db->fetchByAssoc($result)) {
                $records[] = array(
                    "id"     => $row["id"],
                    "module" => $row["module"],
                    "name"   => $GLOBALS["locale"]->getLocaleFormattedName($row["first_name"], $row["last_name"]),
                    "email"  => $row["email_address"],
                );
            }
        }

        return $records;
    }

    /**
     * This function accepts a recipient that provides one or more of ID, Module, Email, and Name, and tries
     * to resolve any of the fields not provided using the following resolution rules.
     *
     * (1) If ID and module are not present, then use the email address to look for matching records in the database.
     * If a match is found, then include the record's ID and module in the return value. If an email address is
     * associated with more than one record, then return the first record.
     *
     * (2) If ID and module are present, then first validate the existence of the record before including the record's
     * email address and name in the return value. If the record does not exist, then set as unresolved.
     *
     * (3) If an ID is present without a module, then ignore the ID.
     *
     * (4) If an email address and module are present without an ID, then search for records by the email address.
     * Select the record with the matching module if one exists and return that record's values. Otherwise, set as
     * unresolved.
     *
     * (5) If an email address and ID are present without a module, then search for records by the email address.
     * Select the record with an ID matching the supplied ID if one exists. Otherwise, set as unresolved.
     *
     * (6) If an email address is present and no module or ID provided, then search for records by the email address.
     * Select a record with a matching email_address if one exists. Note that the record selected is unpredictable if
     * multiple records exist for the supplied email address.
     *
     * (5) If No ID or Email Address provided, set recipient as unresolved.
     *
     * (6) If a match is found, do not overwrite any parameters with data found on the Bean; the data passed in is
     * prioritized over the data found on the Bean.
     *
     * (7) If no name is associated with the recipient after matching a record, then include the email address as the
     * name.
     *
     * @param $recipient
     * @return array
     */
    public function lookup(array $recipient=array())
    {
        $recipient['resolved'] = false;
        $beanId = null;

        if (!empty($recipient['id']) && empty($recipient['module'])) {
            $beanId = $recipient['id'];
            $recipient['id'] = '';
        }

        if (!empty($recipient['id']) && !empty($recipient['module'])) {
            // If ID and module are present, then resolve recipient using the supplied ID
            $this->lookupRecipientById($recipient);
        } elseif (empty($recipient['id']) && !empty($recipient['email'])) {
            // If ID is Not Present, then then use the email address to look for matching records in the database.
            $this->lookupRecipientByEmailAddress($beanId, $recipient);
        }

        if ($recipient['id'] == '' && $beanId != null && !$recipient['resolved']) {
            // Restore original beanId if it was provided and no other valid ID resolution occurred
            $recipient['id'] = $beanId;
        }

        if ($recipient['resolved'] && empty($recipient['name'])) {
            $recipient['name'] = $recipient['email'];
        }

        return $recipient;
    }

    /**
     * This function looks up and resolves recipients that have the specified
     * ID and module.
     *
     * @param $recipient
     * @return array
     */
    protected function lookupRecipientById(&$recipient)
    {
        $bean = BeanFactory::getBean($recipient['module'], $recipient['id']);
        if (!empty($bean) && !empty($bean->id) && $bean->id == $recipient['id']) {
            $recipient['resolved'] = true;
            if (empty($recipient['name'])) {
                $recipient['name'] = empty($bean->name) ? '' : $bean->name;
            }
            if (empty($recipient['email'])) {
                $recipient['email'] = empty($bean->email1) ? '' : $bean->email1;
            }
        }
    }

    /**
     * This function looks up and resolves recipients that have the specified email address.
     * Multiple rows are resolved according to the Resolution Rules above.
     *
     * @param $recipient
     * @return array
     */
    protected function lookupRecipientByEmailAddress($beanId, &$recipient)
    {
        global $beanList;

        if ($this->sugarEmail == null) {
            $this->sugarEmail = new SugarEmailAddress();
        }
        $beans = $this->sugarEmail->getBeansByEmailAddress($recipient['email']);
        if (!empty($beans)) {
            if ($this->beanNames == null) {
                // array_flip is done lazily as this method may be called many times per object instance.
                // We use array_flip as a performance enhancement providing Keyed Lookup over Sequential Lookup
                $this->beanNames = array_flip($beanList);
            }
            foreach ($beans AS $bean) {
                $beanType = get_class($bean);
                if (isset($this->beanNames[$beanType])) {
                    $module = $this->beanNames[$beanType];
                    if (empty($recipient['module']) || $module == $recipient['module']) {
                        if ($beanId == null || ($beanId == $bean->id)) {
                            $recipient['resolved'] = true;
                            $recipient['module'] = $module;
                            $recipient['id'] = $bean->id;
                            if (empty($recipient['email'])) {
                                $recipient['email'] = empty($bean->email1) ? '' : $bean->email1;
                            }
                            if (empty($recipient['name'])) {
                                $recipient['name'] = empty($bean->name) ? '' : $bean->name;
                            }
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * An InboundEmail object must be set up a particular way in order to make the EmailUI::getRelatedEmail calls found
     * in EmailRecipientsService::findCount and EmailRecipientsService::find. The InboundEmail object is also needed to
     * complete other logic found in those methods.
     *
     * @see EmailRecipientsService::findCount()
     * @see EmailRecipientsService::find()
     * @return InboundEmail
     */
    protected function setUpForRelatedEmailQueries()
    {
        $email = new Email;
        $email->email2init();

        $inboundEmail        = new InboundEmail;
        $inboundEmail->email = $email;

        return $inboundEmail;
    }
}
