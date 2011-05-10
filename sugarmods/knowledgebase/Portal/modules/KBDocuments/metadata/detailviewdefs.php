<?php
$viewdefs['KBDocuments']['detailview'] = array (
  'templateMeta' => 
  array (
    'maxColumns' => '2',
    'widths' => 
    array (
      array (
        'label' => '10',
        'field' => '30',
      ),
      array (
        'label' => '10',
        'field' => '30',
      ),
    ),
  ),
  'data' => 
  array (
    array ('kbdocument_name', 'active_date'),
    array (array ('field' => 'description', 'nl2br' => true),
    ),
  ),
);
?>
