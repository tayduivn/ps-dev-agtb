<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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
     * Handles the from_collection, to_collection, cc_collection, and bcc_collection fields for filtering.
     *
     * {@inheritdoc}
     */
    protected static function addFieldFilter(SugarQuery $q, SugarQuery_Builder_Where $where, $filter, $field)
    {
        static $macros = [
            'from_collection' => EmailsFilterApi::MACRO_FROM,
            'to_collection' => EmailsFilterApi::MACRO_TO,
            'cc_collection' => EmailsFilterApi::MACRO_CC,
            'bcc_collection' => EmailsFilterApi::MACRO_BCC,
        ];
        switch ($field) {
            case 'from_collection':
            case 'to_collection':
            case 'cc_collection':
            case 'bcc_collection':
                if (!is_array($filter)) {
                    throw new SugarApiExceptionInvalidParameter("No operators defined for {$field}");
                }

                if (!array_key_exists('$in', $filter)) {
                    throw new SugarApiExceptionInvalidParameter("{$field} requires the use of the \$in operator");
                }

                $supportedOperators = ['$in'];
                $unsupportedOperators = array_diff(array_keys($filter), $supportedOperators);

                if (!empty($unsupportedOperators)) {
                    throw new SugarApiExceptionInvalidParameter(
                        "{$field} does not support these operators: " . implode(', ', $unsupportedOperators)
                    );
                }

                $macro = $macros[$field];

                foreach ($supportedOperators as $op) {
                    static::addParticipantFilter($q, $where, $filter[$op], $macro);
                }

                break;
            default:
                parent::addFieldFilter($q, $where, $filter, $field);
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
     *                 'parent_type' => 'Users',
     *                 'parent_id' => '$current_user_id',
     *             ),
     *             array(
     *                 'parent_type' => 'Contacts',
     *                 'parent_id' => 'fa300a0e-0ad1-b322-9601-512d0983c19a',
     *             ),
     *             array(
     *                 'email_address_id' => 'b0701501-1fab-8ae7-3942-540da93f5017',
     *             ),
     *             array(
     *                 'parent_type' => 'Leads',
     *                 'parent_id' => '73b1087e-4bb6-11e7-acaa-3c15c2d582c6',
     *                 'email_address_id' => 'b651d834-4bb6-11e7-bfcf-3c15c2d582c6',
     *             ),
     *         ),
     *     ),
     * )
     * </code>
     *
     * The above filter definition would return all emails sent by the current user, by the contact whose ID is
     * fa300a0e-0ad1-b322-9601-512d0983c19a, using the email address foo@bar.com, which is referenced by the ID
     * b0701501-1fab-8ae7-3942-540da93f5017, or by the lead whose ID is 73b1087e-4bb6-11e7-acaa-3c15c2d582c6 using the
     * email address biz@baz.com, which is referenced by the ID b651d834-4bb6-11e7-bfcf-3c15c2d582c6. Any number of
     * tuples can be provided in the definition. When the $current_user_id macro is used for the parent_id field, it is
     * swapped for the current user's ID.
     *
     * @param SugarQuery $q The whole SugarQuery object
     * @param SugarQuery_Builder_Where $where The Where part of the SugarQuery object
     * @param array $filter
     * @param string $field The filter to use: $from, $to, $cc, or $bcc.
     * @throws SugarApiExceptionInvalidParameter
     * @throws SugarQueryException
     */
    protected static function addParticipantFilter(SugarQuery $q, SugarQuery_Builder_Where $where, $filter, $field)
    {
        if (!is_array($filter)) {
            throw new SugarApiExceptionInvalidParameter(static::MACRO_FROM . ' requires an array');
        }

        static $links = [
            EmailsFilterApi::MACRO_FROM => 'from',
            EmailsFilterApi::MACRO_TO => 'to',
            EmailsFilterApi::MACRO_CC => 'cc',
            EmailsFilterApi::MACRO_BCC => 'bcc',
        ];
        $link = $links[$field];

        $fta = $q->getFromAlias();
        $joinParams = [
            'joinType' => 'LEFT',
        ];
        $join = $q->join($link, $joinParams);
        $jta = $join->joinName();
        $join->on()->equalsField("{$fta}.id", "{$jta}.email_id");
        $or = $where->queryOr();

        foreach ($filter as $def) {
            if (!is_array($def)) {
                throw new SugarApiExceptionInvalidParameter(
                    "definition for {$field} operator is invalid: must be an array"
                );
            }

            // The `parent_type` and `parent_id` fields must be defined if the `email_address_id` isn't. The `parent_id`
            // field must be defined if the `parent_type` is defined. The `parent_type` field must be defined if the
            // `parent_id` field is defined.
            if (!isset($def['email_address_id']) || isset($def['parent_type']) || isset($def['parent_id'])) {
                if (!isset($def['parent_type'])) {
                    throw new SugarApiExceptionInvalidParameter(
                        "definition for {$field} operator is invalid: parent_type is required"
                    );
                }

                if (!isset($def['parent_id'])) {
                    throw new SugarApiExceptionInvalidParameter(
                        "definition for {$field} operator is invalid: parent_id is required"
                    );
                }
            }

            if (isset($def['parent_id']) && $def['parent_id'] === static::MACRO_CURRENT_USER_ID) {
                $def['parent_id'] = static::$current_user->id;
            }

            $and = $or->queryAnd();
            $and->equals("{$jta}.address_type", $link);

            if (isset($def['email_address_id'])) {
                $and->equals("{$jta}.email_address_id", $def['email_address_id']);
            }

            if (isset($def['parent_type'])) {
                $and->equals("{$jta}.parent_type", $def['parent_type']);
            }

            if (isset($def['parent_id'])) {
                $and->equals("{$jta}.parent_id", $def['parent_id']);
            }
        }
    }
}
