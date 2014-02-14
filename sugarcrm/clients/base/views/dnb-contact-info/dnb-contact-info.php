<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['base']['view']['dnb-contact-info'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_DNB_CONTACT',
            'description' => 'LBL_DNB_CONTACT_DESC',
            'filter' => array(
                'module' => array(
                    'Accounts',
                ),
                'view' => 'record'
            ),
            'config' => array(),
            'preview' => array(),
        ),
    ),
    'custom_toolbar' => array(
        'buttons' => array(
            array(
                'type' => 'dashletaction',
                "css_class" => "btn btn-primary importDNBData hide",
                'name' => 'import_dnb_data',
                "action" => "importDNBData",
                "label" => "LBL_DNB_IMPORT",
            ),
            array(
                "type" => "dashletaction",
                "css_class" => "dashlet-toggle btn btn-invisible minify",
                "icon" => "icon-chevron-down",
                "action" => "toggleMinify",
                "tooltip" => "LBL_DASHLET_MAXIMIZE",
            ),
        )
    )
);
