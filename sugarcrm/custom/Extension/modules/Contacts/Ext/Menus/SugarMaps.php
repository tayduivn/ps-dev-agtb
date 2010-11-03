<?php 
 //WARNING: The contents of this file are auto-generated


/*****************************************************************************
 * The contents of this file are subject to the RECIPROCAL PUBLIC LICENSE
 * Version 1.1 ("License"); You may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 * http://opensource.org/licenses/rpl.php. Software distributed under the
 * License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND,
 * either express or implied.
 *
 * You may:
 * a) Use and distribute this code exactly as you received without payment or
 *    a royalty or other fee.
 * b) Create extensions for this code, provided that you make the extensions
 *    publicly available and document your modifications clearly.
 * c) Charge for a fee for warranty or support or for accepting liability
 *    obligations for your customers.
 *
 * You may NOT:
 * a) Charge for the use of the original code or extensions, including in
 *    electronic distribution models, such as ASP (Application Service
 *    Provider).
 * b) Charge for the original source code or your extensions other than a
 *    nominal fee to cover distribution costs where such distribution
 *    involves PHYSICAL media.
 * c) Modify or delete any pre-existing copyright notices, change notices,
 *    or License text in the Licensed Software
 * d) Assert any patent claims against the Licensor or Contributors, or
 *    which would in any way restrict the ability of any third party to use the
 *    Licensed Software.
 *
 * You must:
 * a) Document any modifications you make to this code including the nature of
 *    the change, the authors of the change, and the date of the change.
 * b) Make the source code for any extensions you deploy available via an
 *    Electronic Distribution Mechanism such as FTP or HTTP download.
 * c) Notify the licensor of the availability of source code to your extensions
 *    and include instructions on how to acquire the source code and updates.
 * d) Grant Licensor a world-wide, non-exclusive, royalty-free license to use,
 *    reproduce, perform, modify, sublicense, and distribute your extensions.
 *
 * The Original Code is: SugarCubed Extensions
 *                       Kenneth brill
 *                       2006-03-04 ken.brill@gmail.com
 *
 * The Initial Developer of the Original Code is SugarCubed / Kenneth Brill
 * Portions created by Kenneth Brill are Copyright (C) 2005 Kenneth Brill
 * All Rights Reserved.
 ********************************************************************************/

global $mod_strings;
global $focus;
global $current_user;

	if(!empty($focus->primary_address_street)) {
//		$module_menu[] =Array("http://maps.msn.com/home.aspx?strt1=$focus->primary_address_street&city1=$focus->primary_address_city&stnm1=$focus->primary_address_state&zipc1=$focus->primary_address_postalcode&cnty1=0", $mod_strings['LBL_MAP'],"Import", 'Contacts');
		$module_menu[] =Array("http://maps.google.com/maps?oi=map&q=$focus->primary_address_street+$focus->primary_address_city+$focus->primary_address_state+$focus->primary_address_postalcode", $mod_strings['LBL_MAP'],"Map", 'Contacts');
		if(!empty($current_user->address_street)) {
				$module_menu[] =Array("http://maps.google.com/maps?saddr=$focus->primary_address_street+$focus->primary_address_city+$focus->primary_address_state+$focus->primary_address_postalcode&daddr=$current_user->address_street+$current_user->address_city+$current_user->address_state+$current_user->address_postalcode", $mod_strings['LBL_DIRECTIONS'],"Directions", 'Contacts');
		}
	}

?>