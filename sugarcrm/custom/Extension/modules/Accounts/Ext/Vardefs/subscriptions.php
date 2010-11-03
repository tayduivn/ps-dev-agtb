<?php

$dictionary['Account']['fields']['subscriptions'] =   array (
    'name' => 'subscriptions',
    'type' => 'link',
    'relationship' => 'accounts_subscriptions',
    'module'=>'subscriptions',
    'bean_name'=>'Subscriptions',
    'source'=>'non-db',
    'vname'=>'LBL_SUBSCRIPTIONS',
);

$dictionary['Account']['relationships']['accounts_subscriptions'] = array(
                        'lhs_module'=> 'Accounts',
                        'lhs_table'=> 'accounts',
                        'lhs_key' => 'id',
                        'rhs_module'=> 'Subscriptions',
                        'rhs_table'=> 'subscriptions',
                        'rhs_key' => 'account_id',
                        'relationship_type'=>'one-to-many'
);

