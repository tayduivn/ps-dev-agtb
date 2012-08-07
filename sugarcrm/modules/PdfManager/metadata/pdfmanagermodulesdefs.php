<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//FILE SUGARCRM flav=pro ONLY

// For $bannedPdfManagerFieldsAndLinks, list of banned Fields and Links by module for PdfManager
/*
        $bannedPdfManagerFieldsAndLinks['moduleName'] = array(
            'fields' => array(
                'fieldName1',
                'fieldName2',
            ),
            'relationships' => array(
                'relationshipName1',
                'relationshipName2',
            ),
        );
*/

$bannedPdfManagerFieldsAndLinks['Accounts'] = array (
    'fields' => array(
        'billing_address_street_2',
        'billing_address_street_3',
        'billing_address_street_4',
        'shipping_address_street_2',
        'shipping_address_street_3',
        'shipping_address_street_4',        
    ),
);
$bannedPdfManagerFieldsAndLinks['Contacts'] = array (
    'fields' => array(
        'primary_address_street_2',
        'primary_address_street_3',
        'alt_address_street_2',
        'alt_address_street_3',
    ),
);
$bannedPdfManagerFieldsAndLinks['Leads'] = array (
    'fields' => array(
        'primary_address_street_2',
        'primary_address_street_3',
        'alt_address_street_2',
        'alt_address_street_3',
    ),
);
$bannedPdfManagerFieldsAndLinks['Employees'] = array (
    'relationships' => array(
        'holidays',
        'oauth_tokens',
    ),
);

// For $bannedPdfManagerModules, list of banned modules for PdfManager
$bannedPdfManagerModules[] = 'KBDocuments';
$bannedPdfManagerModules[] = 'Users';
$bannedPdfManagerModules[] = 'Employees';