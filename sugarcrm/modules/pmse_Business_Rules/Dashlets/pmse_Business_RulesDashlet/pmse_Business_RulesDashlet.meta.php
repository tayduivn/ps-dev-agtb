<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

 
global $app_strings;

$dashletMeta['pmse_Business_RulesDashlet'] = array('module'		=> 'pmse_Business_Rules',
										  'title'       => translate('LBL_HOMEPAGE_TITLE', 'pmse_Business_Rules'), 
                                          'description' => 'A customizable view into pmse_Business_Rules',
                                          'icon'        => 'icon_pmse_Business_Rules_32.gif',
                                          'category'    => 'Module Views');