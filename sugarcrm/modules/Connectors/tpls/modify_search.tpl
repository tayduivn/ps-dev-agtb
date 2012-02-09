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
*}
<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>
<script type="text/javascript" src="{sugar_getjspath file='modules/Connectors/Connector.js'}"></script>
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Connectors/tpls/tabs.css'}"/>

{literal}

<script language="javascript">

var _sourceArray = new Array();

var SourceTabs = {

    init : function() {    
         _tabView = new YAHOO.widget.TabView();

    	{/literal}      
    		{counter assign=source_count start=0 print=0} 
	        {foreach name=connectors from=$SOURCES item=source}   
	            {counter assign=source_count}
		{literal} 
		       	tab = new YAHOO.widget.Tab({
			        label: '{/literal}{$source.name}{literal} ',
			        dataSrc: {/literal}'index.php?module=Connectors&action=SearchProperties&source_id={$source.id}'{literal},
			        cacheData: true,
			        {/literal}
			        {if $source_count == 1}
			        active: true
			        {else}
			         active: false
			        {/if}
			        {literal}
			    });
			    _sourceArray[{/literal}{$source_count}{literal}-1] = '{/literal}{$source.id}';
			    tab.addListener('contentChange', SourceTabs.tabContentChanged);
			    tab.id = '{$source.id}';
			    _tabView.addTab(tab);
	       {/foreach}
		  {literal} 
  		_tabView.appendTo('container'); 
    },
    
    tabContentChanged: function(info) { 
    	tab = _tabView.get('activeTab');
        SUGAR.util.evalScript(tab.get('content'));  
    },

    fitContainer: function() {
		_tabView = SourceTabs.getTabView();
		content_div = _tabView.getElementsByClassName('yui-content', 'div')[0];
		content_div.style.overflow='auto'; 
		content_div.style.height='405px';  
    },
     
     getTabView : function() {
        return _tabView;
     }
}
YAHOO.util.Event.onDOMReady(SourceTabs.init);
</script>
{/literal}
<form name="ModifySearch" method="POST">
<input type="hidden" name="modify" value="true">
<input type="hidden" name="module" value="Connectors">
<input type="hidden" name="action" value="SaveModifySearch">
<input type="hidden" name="source_id" value="">

{counter assign=source_count start=0 print=0} 
{if count($SOURCES) == 0}
	{$MOD.ERROR_NO_SEARCHDEFS_MAPPED}
{else}
	{foreach name=connectors from=$SOURCES key=name item=source}  
	{counter assign=source_count}
	<input type="hidden" name="source{$source_count}" value="{$source.id}">  
	{/foreach}
{/if}
<input type="hidden" name="search_values" value="">
<input type="hidden" name="search_sources" value="">
<input type="hidden" name="reset_to_default" value="">

<table border="0" class="actionsContainer">
<tr><td>
<input id="connectors_top_save" title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button" onclick="calculateValues();" type="submit" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
<input id="connectors_top_cancel" title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="document.ModifySearch.action.value='ConnectorSettings'; document.ModifySearch.module.value='Connectors';" type="submit" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
</td></tr>
</table>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td>
<div>
<div id="container" style="height: 465px">
</div>
</div>
</td></tr>
</table>
<table border="0" class="actionsContainer">
<tr><td>
<input id="connectors_bottom_save" title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button" onclick="calculateValues();" type="submit" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
<input id="connectors_bottom_cancel" title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="document.ModifySearch.action.value='ConnectorSettings'; document.ModifySearch.module.value='Connectors';" type="submit" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
</td></tr>
</table>
</form>


<script type="text/javascript">
{literal}
function calculateValues() {
    tabview = SourceTabs.getTabView();
    search_vals = ''
    source_vals = '';
    sources = new Array();
    //Get the source divs
    elements = tabview.getElementsByClassName('sources_table_div', 'div');
    for(el in elements) {
        if(typeof elements[el] == 'function') {
           continue;
        }
    
        div_id = elements[el].getAttribute('id');
        source_id = div_id.substr(0, div_id.indexOf('_add_tables'));
        if(sources[source_id] == null) {
           sources[source_id] = source_id;
           source_vals += ',' + source_id;
        }
    }
    
    //Get the enabled div elements
    elements = tabview.getElementsByClassName('enabled_workarea', 'div');    
    for(el in elements) {
        if(typeof elements[el] == 'function') {
           continue;
        }
    
        //Get the li elements 
 		enabled_list = YAHOO.util.Dom.getElementsByClassName('noBullet2', 'li', elements[el]);
        for(li in enabled_list) {
            if(typeof enabled_list[li] != 'function') {
                search_vals += ',' + enabled_list[li].getAttribute('id');  	
            }
        }
    }
    
    document.ModifySearch.search_values.value = search_vals != '' ? search_vals.substr(1,search_vals.length) : '';
    document.ModifySearch.search_sources.value = source_vals != '' ? source_vals.substr(1, source_vals.length) : '';
}

YAHOO.util.Event.onDOMReady(SourceTabs.fitContainer);
{/literal}
</script>