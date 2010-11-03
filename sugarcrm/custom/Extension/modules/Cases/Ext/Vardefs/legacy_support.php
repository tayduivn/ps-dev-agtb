<?php
// Putting this in for IT Request 4615 - There are old relationships stored in the db (for workflow?) that refer to this old relationship
//                                       which was changed from 'account' to 'accounts' in 5.0 for consistency reasons
//                                       Since we still refer to the old ones, we need to maintain this until they are removed
$dictionary['Case']['fields']['account'] = array(
      'name' => 'account',
      'type' => 'link',
      'relationship' => 'account_cases',
      'link_type' => 'one',
      'side' => 'right',
      'source' => 'non-db',
      'vname' => 'LBL_ACCOUNT',
);
?>
