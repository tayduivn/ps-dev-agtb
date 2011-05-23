<?php
// BEGIN sadek - SIMILAR OPPORTUNITIES SUBPANEL
$dictionary["Opportunity"]["fields"]["related_opportunities"] = array (
  'name' => 'related_opportunities',
  'type' => 'link',
  'relationship' => 'related_opportunities',
  'source' => 'non-db',
  'rel_fields'=>array('score'=>array('type'=>'int',)),
  'vname' => 'LBL_RELATED_OPPORTUNITIES_TITLE',
);

$dictionary['Opportunity']['fields']['score'] =
        array (
            'name' => 'score',
            'rname' => 'id',
            'relationship_fields'=>array('score' => 'score'),
            'vname' => 'LBL_ACCOUNT_NAME',
            'type' => 'relate',
            'link' => 'related_opportunities',
            'link_type' => 'relationship_info',
            'join_link_name' => 'related_opportunities',
            'source' => 'non-db',
            'importable' => 'false',
            'duplicate_merge'=> 'disabled',
            'studio' => array('listview' => false),
        );
