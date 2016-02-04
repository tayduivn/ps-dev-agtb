<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

global $app_strings;

$dashletMeta['MyDocumentsDashlet'] = array('module'      => 'Documents',
										  'title'       => translate('LBL_HOMEPAGE_TITLE', 'Documents'), 
                                          'description' => 'A customizable view into Documents',
                                          'category'    => 'Module Views');
?>