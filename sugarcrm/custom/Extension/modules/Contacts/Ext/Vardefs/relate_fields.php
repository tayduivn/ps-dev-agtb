<?php

$dictionary['Contact']['fields']['account_client_id'] =
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

$dictionary['Contact']['fields']['account_alt_lang_name'] =
                array (
                        'name' => 'account_alt_lang_name',
                        'rname' => 'alt_lang_name',
                        'id_name' => 'account_id',
                        'vname' => 'LBL_ACCOUNT_ALT_LANG_NAME',
                        'type' => 'relate',
                        'link' => 'accounts',
                        'isnull' => 'true',
                        'module' => 'Accounts',
                        'dbType' => 'varchar',
                        'len' => '20',
                        'source' => 'non-db',
                        'unified_search' => false,
                );
