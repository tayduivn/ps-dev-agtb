<?php
$viewdefs['DocumentRevisions']['subpanel'] = array(
    'FILENAME' => array('width' => '40',
                        'customCode' => '<a href="index.php?module=KBDocuments&action=GetAttachment&id={$ID}&ext={$FILE_EXT}&to_pdf=1">{$FILENAME}</a>',
                        'sortable' => false),
    'DATE_ENTERED' => array('width' => '30',
                            'sortable'=> false), 
    'CREATED_BY' => array('width' => '30', 
                          'sortable' => false)

);
?>
