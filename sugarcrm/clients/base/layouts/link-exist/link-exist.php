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

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array(
    'view' => array(
        'name' => 'link-headerpane',
        'action' => 'select'
    )
));
$layout->push('main', array('view' => 'link-moduleselect'));
$layout->push('side', array('layout' => 'sidebar'));
$viewdefs['base']['layout']['link-exist'] = $layout->getLayout();
