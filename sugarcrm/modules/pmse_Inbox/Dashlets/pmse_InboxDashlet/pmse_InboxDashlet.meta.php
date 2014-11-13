<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $app_strings;

$dashletMeta['pmse_InboxDashlet'] = array('module'		=> 'pmse_Inbox',
										  'title'       => translate('LBL_HOMEPAGE_TITLE', 'pmse_Inbox'),
                                          'description' => 'A customizable view into pmse_Inbox',
                                          'icon'        => 'icon_pmse_Inbox_32.gif',
                                          'category'    => 'Module Views');