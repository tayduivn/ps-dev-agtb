<?php

// set up the M2 stuff for the Calls Module
// jwhitcraft 3.12.10
//unset($dictionary['Call']['fields']['leads']);

$dictionary['Call']['fields']['direction']['default'] = 'Outbound';

$dictionary['Call']['fields']['leadcontacts'] = array (
    'name' => 'leadcontacts',
    'type' => 'link',
    'relationship' => 'calls_leadcontacts',
    'source'=>'non-db',
    'vname'=>'LBL_LEADS',
    'comment' => 'Used to discover attendees',
);

$dictionary['Call']['fields']['leadcontact_related'] = array(
    'name' => 'leadcontact_related',
    'type' => 'link',
    'relationship' => 'leadcontact_calls',
    'source'=>'non-db',
    'vname'=>'LBL_LEADS',
    'comment' => 'find lead contact related to the call by parent_id',
);

// end jwhitcraft
