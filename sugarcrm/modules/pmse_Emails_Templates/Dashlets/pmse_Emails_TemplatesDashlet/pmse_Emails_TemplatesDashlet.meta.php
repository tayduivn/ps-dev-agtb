<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $app_strings;

$dashletMeta['pmse_Emails_TemplatesDashlet'] = array('module'		=> 'pmse_Emails_Templates',
										  'title'       => translate('LBL_HOMEPAGE_TITLE', 'pmse_Emails_Templates'), 
                                          'description' => 'A customizable view into pmse_Emails_Templates',
                                          'icon'        => 'icon_pmse_Emails_Templates_32.gif',
                                          'category'    => 'Module Views');