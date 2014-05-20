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

$viewdefs['Reports']['base']['filter']['reports'] = array(
    'filters' => array(
        array(
            'id' => 'by_module',
            'name' => 'LBL_FILTER_BY_MODULE',
            'filter_definition' => array(
                array(
                    'module' => array(
                        '$in' => array(),
                    ),
                ),
            ),
            'editable' => true,
            'is_template' => true,
        ),
    ),
);
