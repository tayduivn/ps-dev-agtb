<?php
$viewdefs['KBDocuments']['searchform']['basic'] = array(
    'templateMeta' => array('maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '80')
                                            ),
                            'formId' => 'KBDocumentSearchForm',
                            'formName' => 'KBDocumentSearchForm',
                           ),
    'data' => array(
        array('kbdocument_name'),
        array('keywords'),
    )
);
?>
