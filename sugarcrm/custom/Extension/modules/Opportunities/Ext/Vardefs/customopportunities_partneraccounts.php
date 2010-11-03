<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 95
 * Change teh partner_assigned_to_c to be the id of a new relate field so we can continue to use the relations setup by the dropdown that it used to be
 */

$dictionary["Opportunity"]["fields"]["partner_assigned_to_c"] =
  array (
    'required' => false,
    'name' => 'partner_assigned_to_c',
    'vname' => 'LBL_PARTNER_ASSIGNED_TO_NEW',
    'type' => 'enum',
    'massupdate' => 0,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => 0,
    'reportable' => 1,
    'len' => 36,
    'size' => '20',
  );

