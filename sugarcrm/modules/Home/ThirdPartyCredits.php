<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//NOTE: Under the License referenced above, you are required to leave in all copyright statements in both
//the code and end-user application.
//FILE SUGARCRM flav=pro ONLY



$credits = array(
	'Language Packs' => array(
		'bg_BG' => array (
            'name' => 'Bulgarian Language Pack',
            'author' => 'CreaSoft Ltd',
            'description' => 'Bulgarian Language Pack by CreaSoft',
            'website' => 'www.creasoft.biz',
        ),
        'da_DK'=> array(
        	'name'=>'Danish Language Pack', 
        	'author'=>'20twenty',
        	'description' => 'Danish Language Pack by 20twenty',
            'website' => 'www.20twenty.dk',
        	
        ),
        'de_DE' => array (
            'name' => 'German Language Pack',
            'author' => 'Kinamu',
            'description' => 'German Language Pack by Kinamu',
            'website' => 'www.kinamu.com',
       	),
       	'es_ES' => array (
            'name' => 'Spanish Language Pack',
            'author' => 'RedK',
            'description' => 'Spanish Language Pack by RedK',
            'website' => 'www.redk.net',
       	),
        'fr_FR' => array (
            'name' => 'French Language Pack',
            'author' => 'Synolia',
            'description' => 'French Language Pack by Synolia',
            'website' => 'www.synolia.com',
        ),
        'hu_HU' => array (
            'name' => 'Hungarian Language Pack',
            'author' => 'Infoteka',
            'description' => 'Hungarian Language Pack by Infoteka',
            'website' => 'www.infoteka.hu',
        ),
        'it_it' => array (
            'name' => 'Italian Language Pack',
            'author' => 'OpenSymbol',
            'description' => 'Italian Language Pack by OpenSymbol',
            'website' => 'www.opensymbol.it',
        ),
        'lv_LV' => array (
                    'name' => 'Latvian Language Pack',
                    'author' => 'Exigen Services',
                    'description' => 'Latvian Language Pack by Exigen Services',
                    'website' => 'www.exigenservices.com',
        ),
        'lt_LT' => array (
            'name' => 'Lithuanian Language Pack',
            'author' => 'OptimusCRM',
            'description' => 'Lithuanian Language Pack by OptimusCRM',
            'website' => 'www.optimuscrm.lt',
        ),
        'nb_NO' => array (
            'name' => 'Norwegian Language Pack',
            'author' => 'Redpill Linpro',
            'description' => 'Norwegian Language Pack by Redpill Linpro',
            'website' => 'www.redpill-linpro.se',
        ),
        'nl_NL' => array (
            'name' => 'Dutch Language Pack',
            'author' => 'BrixCRM',
            'description' => 'Dutch Language Pack by BrixCRM',
            'website' => 'www.brixcrm.nl',
        ),
        'pt_PT' => array (
            'name' => 'Portuguese Language Pack',
            'author' => 'DRI',
            'description' => 'Portuguese Language Pack by DRI',
            'website' => 'www.dri.pt',
        ),
        'ro_RO' => array (
            'name' => 'Romanian Language Pack',
            'author' => 'Mycroft System',
            'description' => 'Romanian Language Pack by Mycroft System',
            'website' => 'www.mycroft-system.com',
        ),
        'ru_RU' => array (
            'name' => 'Russian Language Pack',
            'author' => 'Richlode Solutions',
            'description' => 'Russian Language Pack by Richlode Solutions',
            'website' => 'www.richlodesolutions.com',
        ),
        'sv_SE' => array (
            'name' => 'Swedish Language Pack',
            'author' => 'Transagile AB',
            'description' => 'Swedish Language Pack by Transagile',
            'website' => 'www.transagile.com',
        ),
        'tr_TR' => array (
            'name' => 'Turkish Language Pack',
            'author' => 'Ultima',
            'description' => 'Turkish Language Pack by Ultima',
            'website' => 'ultima.com.tr',
        ),
        'en_UK' => array (
            'name' => 'UK English Language Pack',
            'author' => 'Provident CRM',
            'description' => 'UK English Language Pack by Provident CRM',
            'website' => 'www.providentcrm.com',
        ),
        'cs_CZ' => array (
            'name' => 'Czech Language Pack',
            'author' => 'ExtendIT',
            'description' => 'Czech Language Pack by ExtendIT',
            'website' => 'extendit.cz',
        ),
        'et_EE' => array (
            'name' => 'Estonian Language Pack',
            'author' => 'Keynote',
            'description' => 'Estonian Language Pack by Keynote',
            'website' => 'www.keynote.ee',
        ),
        'he_IL' => array (
            'name' => 'Hebrew Language Pack',
            'author' => 'Menahem Lurie Consultancy and IT Management',
            'description' => 'Hebrew Language Pack by Menahem Lurie Consultancy and IT Management',
            'website' => 'www.cyta.co.il',
        ),
        'ja_JP' => array (
            'name' => 'Japanese Language Pack',
            'author' => 'OSSCRM',
            'description' => 'Japanese Language Pack by OSSCRM',
            'website' => 'www.osscrm.com',
        ),
        'pl_PL' => array (
            'name' => 'Portuguese - Portugal Language Pack',
            'author' => 'Optineo',
            'description' => 'Portuguese - Portugal Language Pack by Optineo',
            'website' => 'www.optineo.com',
        ),
        'pt_BR' => array (
            'name' => 'Portuguese - Brazil Language Pack',
            'author' => 'Provident CRM',
            'description' => 'Portuguese - Brazil Language Pack by Provident CRM',
            'website' => 'www.lampadaglobal.com',
        ),
        'ca_ES' => array (
            'name' => 'Catalan Language Pack',
            'author' => 'REDK Ingenieria del Software, SL',
            'description' => 'Catalan Language Pack by REDK Ingenieria del Software, SL',
            'website' => 'redk.net',
        ),
        'sr_RS' => array (
            'name' => 'Serbian Language Pack',
            'author' => 'PS Tech',
            'description' => 'Serbian Language Pack by PS Tech',
            'website' => 'pstech.rs',
        ),
    ),
	'Modules' => array(
		'Twitter Connector' => array (
			'name' => 'Twitter Connector',
			'author' => 'Synolia',
			'description' => 'Twitter Connector by Synolia',
			'website' => 'www.synolia.com',
		),
	),
	/*
	'Themes' => array(
		'theme_id' => array (
			'name' => 'Theme Name',
			'author' => 'Author',
			'description' => 'Description',
			'website' => 'www.website.com',
		),
	)*/
);
?>