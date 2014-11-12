<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $app_strings;

$dashletMeta['pmse_ProjectDashlet'] = array('module'		=> 'pmse_Project',
										  'title'       => translate('LBL_HOMEPAGE_TITLE', 'pmse_Project'), 
                                          'description' => 'A customizable view into pmse_Project',
                                          'icon'        => 'icon_pmse_Project_32.gif',
                                          'category'    => 'Module Views');