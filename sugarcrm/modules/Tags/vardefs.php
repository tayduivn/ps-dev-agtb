<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
********************************************************************************/

SugarAutoLoader::load('modules/Tags/TagsRelatedModulesUtilities.php');
$fields = TagsRelatedModulesUtilities::getRelatedFields();

$dictionary['Tag'] = array(
    'comment' => 'Tagging module',
    'table' => 'tags',
    'audited' => false,
    'activity_enabled' => false,
    'favorites' => false,
    'optimistic_locking' => false,
    'unified_search' => true,
    'full_text_search' => false,
    'unified_search_default_enabled' => true,
    'fields' => $fields,
    'relationships' => array(),
    'indices' => array(),
);

VardefManager::createVardef(
    'Tags',
    'Tag',
    array(
        'basic',
        'external_source'
    )
);

// get rid of favorites field as this is set through basic template
// regardless if the favorites flag is set to false
unset($dictionary['Tag']['fields']['user_favorites']);
