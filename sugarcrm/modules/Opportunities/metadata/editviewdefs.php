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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['Opportunities']['EditView'] = array(
    'templateMeta' => array('maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'javascript' => '{$PROBABILITY_SCRIPT}',
    ),
    'panels' =>array (
        'default' =>
        array (

            array (
                array('name'=>'name'),
                'account_name',
            ),
//BEGIN SUGARCRM flav!=ent ONLY
            array(
                array('name'=>'currency_id','label'=>'LBL_CURRENCY'),
                array('name'=>'date_closed'),
            ),
//END SUGARCRM flav!=ent ONLY
            array (
//BEGIN SUGARCRM flav!=ent ONLY
                array( 'name'=>'amount'),
//END SUGARCRM flav!=ent ONLY
                'opportunity_type',
//BEGIN SUGARCRM flav!=ent ONLY
            ),
            array(
                'best_case',
                'worst_case',
            ),
            array (
                'sales_stage',
//END SUGARCRM flav!=ent ONLY
                'lead_source',
            ),
            array (
//BEGIN SUGARCRM flav!=ent ONLY
                'probability',
//END SUGARCRM flav!=ent ONLY
                'campaign_name',
//BEGIN SUGARCRM flav!=ent ONLY
            ),
            array (
//END SUGARCRM flav!=ent ONLY
                'next_step',
            ),
            array (
                'description',
            ),
        ),

        'LBL_PANEL_ASSIGNMENT' => array(
            array(
                'assigned_user_name',
//BEGIN SUGARCRM flav=pro ONLY
                array('name'=>'team_name'),
//END SUGARCRM flav=pro ONLY
            ),
        ),
    )
);
