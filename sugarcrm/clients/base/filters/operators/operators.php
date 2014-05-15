<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

$viewdefs['base']['filter']['operators'] = array(
    'multienum' => array(
        '$contains' => 'LBL_OPERATOR_CONTAINS',
        '$not_contains' => 'LBL_OPERATOR_NOT_CONTAINS',
    ),
    'enum' => array(
        '$in' => 'LBL_OPERATOR_CONTAINS',
        '$not_in' => 'LBL_OPERATOR_NOT_CONTAINS',
    ),
    'varchar' => array(
        '$equals' => 'LBL_OPERATOR_MATCHES',
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
    ),
    'name' => array(
        '$equals' => 'LBL_OPERATOR_MATCHES',
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
    ),
    'text' => array(
        '$equals' => 'LBL_OPERATOR_MATCHES',
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
    ),
    'textarea' => array(
        '$equals' => 'LBL_OPERATOR_MATCHES',
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
    ),
    'currency' => array(
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
    ),
    'int' => array(
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$in' => 'LBL_OPERATOR_CONTAINS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
    ),
    'double' => array(
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
    ),
    'float' => array(
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
    ),
    'decimal' => array(
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$not_equals' => 'LBL_OPERATOR_NOT_EQUALS',
        '$gt' => 'LBL_OPERATOR_GREATER_THAN',
        '$lt' => 'LBL_OPERATOR_LESS_THAN',
        '$gte' => 'LBL_OPERATOR_GREATER_THAN_OR_EQUALS',
        '$lte' => 'LBL_OPERATOR_LESS_THAN_OR_EQUALS',
        '$between' => 'LBL_OPERATOR_BETWEEN',
    ),
    'date' => array(
        '$equals' => 'LBL_OPERATOR_EQUALS',
        '$lt' => 'LBL_OPERATOR_BEFORE',
        '$gt' => 'LBL_OPERATOR_AFTER',
        'last_7_days' => 'LBL_OPERATOR_LAST_7_DAYS',
        'next_7_days' => 'LBL_OPERATOR_NEXT_7_DAYS',
        'last_30_days' => 'LBL_OPERATOR_LAST_30_DAYS',
        'next_30_days' => 'LBL_OPERATOR_NEXT_30_DAYS',
        'last_month' => 'LBL_OPERATOR_LAST_MONTH',
        'this_month' => 'LBL_OPERATOR_THIS_MONTH',
        'next_month' => 'LBL_OPERATOR_NEXT_MONTH',
        'last_year' => 'LBL_OPERATOR_LAST_YEAR',
        'this_year' => 'LBL_OPERATOR_THIS_YEAR',
        'next_year' => 'LBL_OPERATOR_NEXT_YEAR',
        '$dateBetween' => 'LBL_OPERATOR_BETWEEN',
    ),
    'datetime' => array(
        '$starts' => 'LBL_OPERATOR_EQUALS',
        '$lte' => 'LBL_OPERATOR_BEFORE',
        '$gte' => 'LBL_OPERATOR_AFTER',
        'last_7_days' => 'LBL_OPERATOR_LAST_7_DAYS',
        'next_7_days' => 'LBL_OPERATOR_NEXT_7_DAYS',
        'last_30_days' => 'LBL_OPERATOR_LAST_30_DAYS',
        'next_30_days' => 'LBL_OPERATOR_NEXT_30_DAYS',
        'last_month' => 'LBL_OPERATOR_LAST_MONTH',
        'this_month' => 'LBL_OPERATOR_THIS_MONTH',
        'next_month' => 'LBL_OPERATOR_NEXT_MONTH',
        'last_year' => 'LBL_OPERATOR_LAST_YEAR',
        'this_year' => 'LBL_OPERATOR_THIS_YEAR',
        'next_year' => 'LBL_OPERATOR_NEXT_YEAR',
        '$dateBetween' => 'LBL_OPERATOR_BETWEEN',
    ),
    'datetimecombo' => array(
        '$starts' => 'LBL_OPERATOR_EQUALS',
        '$lte' => 'LBL_OPERATOR_BEFORE',
        '$gte' => 'LBL_OPERATOR_AFTER',
        'last_7_days' => 'LBL_OPERATOR_LAST_7_DAYS',
        'next_7_days' => 'LBL_OPERATOR_NEXT_7_DAYS',
        'last_30_days' => 'LBL_OPERATOR_LAST_30_DAYS',
        'next_30_days' => 'LBL_OPERATOR_NEXT_30_DAYS',
        'last_month' => 'LBL_OPERATOR_LAST_MONTH',
        'this_month' => 'LBL_OPERATOR_THIS_MONTH',
        'next_month' => 'LBL_OPERATOR_NEXT_MONTH',
        'last_year' => 'LBL_OPERATOR_LAST_YEAR',
        'this_year' => 'LBL_OPERATOR_THIS_YEAR',
        'next_year' => 'LBL_OPERATOR_NEXT_YEAR',
        '$dateBetween' => 'LBL_OPERATOR_BETWEEN',
    ),
    'bool' => array(
        '$equals' => 'LBL_OPERATOR_IS'
    ),
    'relate' => array(
        '$equals' => 'LBL_OPERATOR_IS',
        '$not_equals' => 'LBL_OPERATOR_IS_NOT',
    ),
    'teamset' => array(
        '$equals' => 'LBL_OPERATOR_IS',
        '$not_equals' => 'LBL_OPERATOR_IS_NOT',
    ),
    'phone' => array(
        '$starts' => 'LBL_OPERATOR_STARTS_WITH',
        '$equals' => 'LBL_OPERATOR_IS',
    )
);
