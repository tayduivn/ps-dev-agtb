{*

/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: JotPadDashlet.tpl 16320 2006-08-23 00:15:55Z awu $

*}
<table width='100%' class="listView">
<td align='left' nowrap><b>Show On Login:</b><input type="checkbox" name="splash" onchange="{literal}if(this.checked){this.value = 1}else{this.value=0};{/literal}SugarCards.changeSpashScreen('{$id}', this.value);" value="1" {$checked }>
</td>
<td align='center'>Click the card area to see a new card</td>
<td align='right' class = 'listViewPaginationTdS1' id ='listViewPaginationButtons' nowrap>
<button class='button' onclick="SugarCards.getImage('{$id}', 1);"> {$start}</button>
<button class='button' onclick="SugarCards.getImage('{$id}', parseInt(document.getElementById('{$id}_cardnum').value) - 1);"> {$prev}</button>
<input type="text" autocomplete="off" id="{$id}_cardnum"  value="{$img.number}" size=2 onchange="SugarCards.getImage('{$id}', this.value);"> of {$endIndex}
<button class='button' onclick="SugarCards.getImage('{$id}', parseInt(document.getElementById('{$id}_cardnum').value) + 1);"> {$next}</button>
<button class='button' onclick="SugarCards.getImage('{$id}', {$endIndex});"> {$end}</button>
</td></tr></table>
<img id='img{$id}' src='' style='width:100%;height:426px' onclick="SugarCards.getImage('{$id}');">

<img id='loading{$id}' src='{$img.image}' style='display:none' onload='document.getElementById("img{$id}").src = this.src;'>
