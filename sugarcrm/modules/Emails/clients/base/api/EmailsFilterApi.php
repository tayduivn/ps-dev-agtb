<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'clients/base/api/FilterApi.php';

class EmailsFilterApi extends FilterApi
{
    const MACRO_CURRENT_USER_ID = '$current_user_id';
    const MACRO_FROM = '$from';
    const MACRO_TO = '$to';
    const MACRO_CC = '$cc';
    const MACRO_BCC = '$bcc';

    /**
     * Registers Emails-specific Filter API routes for all generic Filter API routes.
     *
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        $endpoints = parent::registerApiRest();

        foreach ($endpoints as $name => &$endpoint) {
            // Replace all occurrences of the <module> variable in the path with "Emails."
            foreach ($endpoint['path'] as $i => $param) {
                if ($param === '<module>') {
                    $endpoint['path'][$i] = 'Emails';
                }
            }

            // Replace the base long help with one for Emails that documents the additional filters.
            if ($endpoint['longHelp'] === 'include/api/help/module_filter_get_help.html') {
                $endpoint['longHelp'] = 'modules/Emails/clients/base/api/help/emails_filter_get_help.html';
            }
        }

        return $endpoints;
    }

    /**
     * Adds the $from macro to the list of possible filter macros.
     *
     * {@inheritdoc}
     */
    protected static function addFilter($field, $filter, SugarQuery_Builder_Where $where, SugarQuery $q)
    {
        switch ($field) {
            case EmailsFilterApi::MACRO_FROM:
            case EmailsFilterApi::MACRO_TO:
            case EmailsFilterApi::MACRO_CC:
            case EmailsFilterApi::MACRO_BCC:
                static::addParticipantFilter($q, $where, $filter, $field);
                break;
            default:
                parent::addFilter($field, $filter, $where, $q);
        }
    }

    /**
     * This function adds a from, to, cc, or bcc filter to the sugar query based on the value of `$field`.
     *
     * <code>
     * array(
     *     'filter' => array(
     *         '$from' => array(
     *             array(
     *                 'participant_module' => 'Users',
     *                 'participant_id' => '$current_user_id',
     *             ),
     *             array(
     *                 'participant_module' => 'Contacts',
     *                 'participant_id' => 'fa300a0e-0ad1-b322-9601-512d0983c19a',
     *             ),
     *             array(
     *                 'participant_module' => 'EmailAddresses',
     *                 'participant_id' => 'b0701501-1fab-8ae7-3942-540da93f5017',
     *             ),
     *         ),
     *     ),
     * )
     * </code>
     *
     * The above filter definition would return all emails sent by the current user, by the contact whose ID is
     * fa300a0e-0ad1-b322-9601-512d0983c19a, or using the email address foo@bar.com, which is referenced by the ID
     * b0701501-1fab-8ae7-3942-540da93f5017. Any number of tuples can be provided in the definition. When the
     * $current_user_id macro is used for the participant_id field, it is swapped for the current user's ID.
     *
     * @param SugarQuery $q The whole SugarQuery object
     * @param SugarQuery_Builder_Where $where The Where part of the SugarQuery object
     * @param array $filter
     * @param string $field The filter to use: $from, $to, $cc, or $bcc.
     * @throws SugarApiExceptionInvalidParameter
     */
    protected static function addParticipantFilter(SugarQuery $q, SugarQuery_Builder_Where $where, $filter, $field)
    {
        if (!is_array($filter)) {
            throw new SugarApiExceptionInvalidParameter(static::MACRO_FROM . ' requires an array');
        }

        static $roles = array(
            EmailsFilterApi::MACRO_FROM => 'from',
            EmailsFilterApi::MACRO_TO => 'to',
            EmailsFilterApi::MACRO_CC => 'cc',
            EmailsFilterApi::MACRO_BCC => 'bcc',
        );

        $fta = $q->getFromAlias();
        $jta = $q->getJoinTableAlias('emails_participants', false, false);
        $joinParams = array(
            'alias' => $jta,
        );
        $join = isset($q->join[$jta]) ? $q->join[$jta] : $q->joinTable('emails_participants', $joinParams);
        $join->on()->equalsField("{$fta}.id", "{$jta}.email_id");
        $or = $where->queryOr();

        foreach ($filter as $def) {
            if (!is_array($def)) {
                throw new SugarApiExceptionInvalidParameter(
                    "definition for {$field} operation is invalid: must be an array"
                );
            }

            if (!isset($def['participant_module'])) {
                throw new SugarApiExceptionInvalidParameter(
                    "definition for {$field} operation is invalid: participant_module is required"
                );
            }

            if (!isset($def['participant_id'])) {
                throw new SugarApiExceptionInvalidParameter(
                    "definition for {$field} operation is invalid: participant_id is required"
                );
            }

            if ($def['participant_id'] === static::MACRO_CURRENT_USER_ID) {
                $def['participant_id'] = static::$current_user->id;
            }

            $or->queryAnd()
                ->equals("{$jta}.role", $roles[$field])
                ->equals("{$jta}.participant_module", $def['participant_module'])
                ->equals("{$jta}.participant_id", $def['participant_id']);
        }
    }
}
