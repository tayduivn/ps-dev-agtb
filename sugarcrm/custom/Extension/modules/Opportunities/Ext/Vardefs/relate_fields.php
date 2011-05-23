<?php

$dictionary['Opportunity']['fields']['account_client_id'] =
                array (
                        'name' => 'account_client_id',
                        'rname' => 'client_id',
                        'id_name' => 'account_id',
                        'vname' => 'LBL_ACCOUNT_CLIENT_ID',
                        'type' => 'relate',
                        'link' => 'accounts',
                        'isnull' => 'true',
                        'module' => 'Accounts',
                        'dbType' => 'varchar',
                        'len' => '20',
                        'source' => 'non-db',
                        'unified_search' => false,
                );

/**
 *  sadek NOTE: This relationship does not support listview.
 *    Its sole purpose is to display the related opportunity description on detailviews
 *    If we want to support it on listviews, related_opportunity_c should be a field
 *    on the base table. We will also have to manually create opportunity_id on the base table
 *    as well. Then the id_name column in this definition should refer to opportunity_id.
 *    Also, the link value should change to use the correct relationship.
 **/ 
$dictionary['Opportunity']['fields']['related_opportunity_description'] =
                array (
                        'name' => 'related_opportunity_description',
                        'rname' => 'description',
                        'id_name' => 'opportunity_id_c',
                        'vname' => 'LBL_RELATED_OPPORTUNITY_DESCRIPTION',
                        'type' => 'relate',
                        'link' => 'related_opportunity_c',
                        'isnull' => 'true',
                        'module' => 'Opportunities',
                        'dbType' => 'varchar',
                        'len' => '255',
                        'source' => 'non-db',
                        'unified_search' => false,
                );
