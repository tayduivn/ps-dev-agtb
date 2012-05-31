<?php
//FILE SUGARCRM flav=pro ONLY
$module_name = 'PdfManager';
$viewdefs[$module_name] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
          3 => 'FIND_DUPLICATES',
        ),
        'hideAudit' => true,
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
          1 => 'team_name',
        ),
        1 => 
        array (
          0 => 'description',
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'base_module',
            'studio' => 'visible',
            'label' => 'LBL_BASE_MODULE',
          ),
          1 => 
          array (
            'name' => 'published',
            'studio' => 'visible',
            'label' => 'LBL_PUBLISHED',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'field',
            'studio' => 'visible',
            'label' => 'LBL_FIELD',
          ),
          1 => '',
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'body_html',
            'studio' => 'visible',
            'label' => 'LBL_BODY_HTML',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'header_image',
            'studio' => 'visible',
            'label' => 'LBL_HEADER_IMAGE',
          ),
          1 => '',
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
          ),
        ),
      ),
    ),
  ),
);
