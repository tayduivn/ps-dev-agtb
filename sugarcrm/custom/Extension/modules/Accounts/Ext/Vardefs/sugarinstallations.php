<?php

/* SADEK 2008-04-03 - REMOVED SUBPANEL SINCE WE NO LONGER USE THIS MODULE
$dictionary['Account']['fields']['download_keys'] =   array (
        'name' => 'download_keys',
    'type' => 'link',
    'relationship' => 'download_keys_accounts',
    'module'=>'DownloadKeys',
    'bean_name'=>'DownloadKey',
    'source'=>'non-db',
                'vname'=>'LBL_DOWNLOAD_KEYS',
);
*/
$dictionary['Account']['fields']['sugar_installations'] =   array (
        'name' => 'sugar_installations',
    'type' => 'link',
    'relationship' => 'sugar_installations_accounts',
    'module'=>'SugarInstallations',
    'bean_name'=>'SugarInstallation',
    'source'=>'non-db',
        'vname'=>'LBL_SUGAR_INSTALLATIONS',
);

/* SADEK 2008-04-03 - REMOVED SUBPANEL SINCE WE NO LONGER USE THIS MODULE
$dictionary['Account']['relationships']['download_keys_accounts'] = array(
                        'lhs_module'=> 'Accounts',
                        'lhs_table'=> 'accounts',
                        'lhs_key' => 'id',
                        'rhs_module'=> 'DownloadKeys',
                        'rhs_table'=> 'download_keys',
                        'rhs_key' => 'account_id',
                        'relationship_type'=>'one-to-many'
);
*/
$dictionary['Account']['relationships']['sugar_installations_accounts'] = array(
                        'lhs_module'=> 'Accounts',
                        'lhs_table'=> 'accounts',
                        'lhs_key' => 'id',
                        'rhs_module'=> 'SugarInstallations',
                        'rhs_table'=> 'sugar_installations',
                        'rhs_key' => 'account_id',
                        'relationship_type'=>'one-to-many'
);


?>
