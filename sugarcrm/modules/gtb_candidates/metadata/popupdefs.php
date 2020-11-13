<?php
$popupMeta = array (
    'moduleMain' => 'gtb_candidates',
    'varName' => 'gtb_candidates',
    'orderBy' => 'gtb_candidates.first_name, gtb_candidates.last_name',
    'whereClauses' => array (
  'name' => 'gtb_candidates.name',
  'title' => 'gtb_candidates.title',
  'gtb_cluster' => 'gtb_candidates.gtb_cluster',
  'org_unit' => 'gtb_candidates.org_unit',
  'gtb_function' => 'gtb_candidates.gtb_function',
),
    'searchInputs' => array (
  2 => 'name',
  3 => 'title',
  4 => 'gtb_cluster',
  5 => 'org_unit',
  6 => 'gtb_function',
),
    'searchdefs' => array (
  'name' => 
  array (
    'type' => 'fullname',
    'label' => 'LBL_NAME',
    'width' => 10,
    'name' => 'name',
  ),
  'title' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_TITLE',
    'width' => 10,
    'name' => 'title',
  ),
  'gtb_cluster' => 
  array (
    'type' => 'enum',
    'label' => 'LBL_GTB_CLUSTER',
    'width' => 10,
    'name' => 'gtb_cluster',
  ),
  'org_unit' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_ORG_UNIT',
    'width' => 10,
    'name' => 'org_unit',
  ),
  'gtb_function' => 
  array (
    'type' => 'enum',
    'label' => 'LBL_GTB_FUNCTION',
    'width' => 10,
    'name' => 'gtb_function',
  ),
),
    'listviewdefs' => array (
  'NAME' => 
  array (
    'type' => 'fullname',
    'label' => 'LBL_NAME',
    'width' => 10,
    'default' => true,
    'name' => 'name',
  ),
  'TITLE' => 
  array (
    'type' => 'varchar',
    'default' => true,
    'label' => 'LBL_TITLE',
    'width' => 10,
    'name' => 'title',
  ),
  'GTB_CLUSTER' => 
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_GTB_CLUSTER',
    'width' => 10,
    'name' => 'gtb_cluster',
  ),
  'ORG_UNIT' => 
  array (
    'type' => 'varchar',
    'default' => true,
    'label' => 'LBL_ORG_UNIT',
    'width' => 10,
    'name' => 'org_unit',
  ),
  'GTB_FUNCTION' => 
  array (
    'type' => 'enum',
    'default' => true,
    'label' => 'LBL_GTB_FUNCTION',
    'width' => 10,
    'name' => 'gtb_function',
  ),
),
);
