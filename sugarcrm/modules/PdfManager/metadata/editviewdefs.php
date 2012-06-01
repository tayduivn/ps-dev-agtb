<?php
//FILE SUGARCRM flav=pro ONLY
$module_name = 'PdfManager';
$viewdefs[$module_name] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
        'form' => array(
                            'footerTpl' => 'modules/PdfManager/tpls/EditViewFooter.tpl',
                            'enctype'=>'multipart/form-data',
                        ),
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
      'syncDetailEditViews' => true,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 'name',
          1 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'display' => true,
            ),
          ),
        ),
        1 => 
        array (
          0 => array(   'name' => 'description',
                        'displayParams' => array('rows' => 1)
                    ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'base_module',
            'label' => 'LBL_BASE_MODULE',
            'popupHelp' => 'LBL_BASE_MODULE_POPUP_HELP',
          ),
          1 => 
          array (
            'name' => 'published',
            'label' => 'LBL_PUBLISHED',
            'popupHelp' => 'LBL_PUBLISHED_POPUP_HELP',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'field',
            'label' => 'LBL_FIELD',
            'popupHelp' => 'LBL_FIELD_POPUP_HELP',
          ),
          1 => '',
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'body_html',
            'label' => 'LBL_BODY_HTML',
            'popupHelp' => 'LBL_BODY_HTML_POPUP_HELP',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'header_image',
            'label' => 'LBL_HEADER_IMAGE',
          ),
        ),
      ),
      'lbl_editview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'author',
            'label' => 'LBL_AUTHOR',
          ),
          1 => 
          array (
            'name' => 'title',
            'label' => 'LBL_TITLE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'subject',
            'label' => 'LBL_SUBJECT',
          ),
          1 => 
          array (
            'name' => 'keywords',
            'label' => 'LBL_KEYWORDS',
            'popupHelp' => 'LBL_KEYWORDS_POPUP_HELP'
          ),
        ),
      ),
    ),
  ),
);
