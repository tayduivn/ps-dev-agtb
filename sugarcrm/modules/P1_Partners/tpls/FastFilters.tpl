{*

/**
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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

 */


*}

<script>
{literal}

function in_array(string, array)
{
   for (j = 0; j < array.length; j++)
   {
      if(array[j] == string)
      {
         return true;
      }
   }
return false;
}




function filterList(f,v) {
	semantic = detectMIE();
	var searchf = document.getElementById('search_form');
	var els = semantic ? document.forms['search_form'].elements : searchf.elements;
	document.forms['search_form'].reset();
	var ignored_elements = new Array("searchFormTab","module","action","query");
	for(i=0; i<els.length; i++)
	{	
		var fname = els[i].name;
		var ftype = els[i].type;
		if(!in_array(fname,ignored_elements)) {	
			field_type = ftype.toLowerCase();
			 switch(field_type) {
				case "text":
				case "password":
				case "textarea":
				case "hidden":
				els[i].value = "";
				 break;
				   
				case "radio":
				case "checkbox":
				if (els[i].checked) {
					  els[i].checked = false;
				}
				break;

				case "select-one":
				case "select-multi":
				case "select-multiple":
				els[i].selectedIndex = -1;
				break;

				default:
				break;
			 }
			
		}
	}

	if(document.search_form.searchFormTab.value == 'basic_search') {
		if(document.getElementById(f+"_basic")) {
			document.getElementById(f+"_basic").value = v;
		} else {
			basic = document.createElement("input");
			basic.setAttribute("name",f+"_basic");
			basic.setAttribute("value",v);
			basic.setAttribute("type","hidden");
			document.getElementById('P1_Partnersbasic_searchSearchForm').appendChild(basic);
		}
	} else {
		if(document.getElementById(f+"_advanced")) {
			document.getElementById(f+"_advanced").value = v;
		} else {
			advanced = document.createElement("input");
			advanced.setAttribute("name",f+"_advanced");
			advanced.setAttribute("value",v);
			advanced.setAttribute("type","hidden");
			document.getElementById('P1_Partnersadvanced_searchSearchForm').appendChild(advanced);
		}
	}
	if(f == "accepted_by_partner_c") {
		open_tasks = document.createElement("input");
		open_tasks.setAttribute("name","open_tasks");
		open_tasks.setAttribute("value","1");
		open_tasks.setAttribute("type","hidden");
		document.getElementById('P1_Partnersadvanced_searchSearchForm').appendChild(open_tasks);
	}
	document.search_form.submit();
	
}
{/literal}
</script>
<table cellpadding='0' cellspacing='0' width='100%' border='0' style="margin-top: 10px;">
<tr>
<td><b>{$FILTER_BY}:</b>&nbsp;&nbsp;{if $POST_SIXTY == '0'}<a href="javascript: filterList('sixtymin_opp_c',1);" title="{$SIXTYMIN_OPP}">{$QF_SIXTYMIN_OPP}</a>{else}<b>{$QF_SIXTYMIN_OPP}</b>{/if} | {if $POST_REJECTED == '0'}<a href="javascript: filterList('accepted_by_partner_c','R');" title="{$REJECTED_OPP}">{$QF_REJECTED_OPP}</a>{else}<b>{$QF_REJECTED_OPP}</B>{/if} | {if $POST_MATURE == '0'}<a href="javascript: filterList('partner_contact_notified_c',1);" title="{$MATURE_OPP}">{$QF_MATURE_OPP}</a>{else}<b>{$QF_MATURE_OPP}</b>{/if} | {if $POST_CONFLICT == '0'}<a href="javascript: filterList('conflict_c',1);" title="{$QF_CONFLICT}">{$QF_CONFLICT}</a>{else}<b>{$QF_CONFLICT}</b>{/if}</td>
<td align="right"><b>{$LEGEND}:</b> <div style="display: inline; background-color: #B6B6FF; height: 16px; width: 16px; border: 1px solid #ccc; margin-right: 3px; margin-left: 5px;"><img src="{sugar_getimagepath file='spacer.gif'}" height="16" width="16" border="0"></div> {$SIXTYMIN_OPP}
<div style="display: inline; background-color: #FFB6B6; height: 16px; width: 16px; border: 1px solid #ccc; margin-right: 3px; margin-left: 5px;"><img src="{sugar_getimagepath file='spacer.gif'}" height="16" width="16" border="0"></div> {$REJECTED_OPP}
<div style="display: inline; background-color: #FFA240; height: 16px; width: 16px; border: 1px solid #ccc; margin-right: 3px; margin-left: 5px;"><img src="{sugar_getimagepath file='spacer.gif'}" height="16" width="16" border="0"></div> {$QF_CONFLICT}</td>
</tr>
</table>


