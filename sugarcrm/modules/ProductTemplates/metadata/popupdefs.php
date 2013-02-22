<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$popupMeta = array (
    'moduleMain' => 'ProductTemplates',
    'varName' => 'ProductTemplate',
    'orderBy' => 'producttemplates.name',
    'whereClauses' => array (
        'name' => 'producttemplates.name',
        'category_name' => 'producttemplates.category_name',
    ),
    'searchInputs' => array (
        'name',
        'category_name',
    ),
    'searchdefs' => array (
        'name',
        'category_name'
    ),
    'listviewdefs' => array (
        'NAME' =>
        array (
            'width' => '30',
            'label' => 'LBL_LIST_NAME',
            'link' => true,
            'default' => true,
            'name' => 'name',
        ),
        'TYPE_NAME' =>
        array (
            'width' => '10',
            'label' => 'LBL_LIST_TYPE',
            'sortable' => true,
            'default' => true,
            'name' => 'type_name',
        ),
        'CATEGORY_NAME' =>
        array (
            'width' => '10',
            'label' => 'LBL_LIST_CATEGORY',
            'sortable' => true,
            'default' => true,
            'name' => 'category_name',
        ),
        'STATUS' =>
        array (
            'width' => '10',
            'label' => 'LBL_LIST_STATUS',
            'default' => true,
            'name' => 'status',
        ),
        'QTY_IN_STOCK' =>
        array (
            'width' => '10',
            'label' => 'LBL_LIST_QTY_IN_STOCK',
            'default' => true,
            'name' => 'qty_in_stock',
        ),
        'COST_PRICE' =>
        array (
            'type' => 'currency',
            'label' => 'LBL_COST_PRICE',
            'currency_format' => true,
            'width' => '10',
            'default' => true,
            'name' => 'cost_price',
        ),
        'LIST_PRICE' =>
        array (
            'type' => 'currency',
            'label' => 'LBL_LIST_PRICE',
            'currency_format' => true,
            'width' => '10',
            'default' => true,
            'name' => 'list_price',
        ),
        'DISCOUNT_PRICE' =>
        array (
            'type' => 'currency',
            'label' => 'LBL_DISCOUNT_PRICE',
            'currency_format' => true,
            'width' => '10',
            'default' => true,
        ),
    ),
);