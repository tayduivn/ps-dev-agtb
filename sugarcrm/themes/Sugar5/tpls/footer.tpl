{*
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
*}
<!--end body panes-->
        </td></tr></table>
    </div>
    <div class="clear"></div>
</div>
<div id="bottomLinks">
{if $AUTHENTICATED}
{$BOTTOMLINKS}
{/if}
</div>
<div id="footer">
	<div id="responseTime">
    	{$STATISTICS}
    </div>
    <div id="copyright">
        {$COPYRIGHT}
    </div>
</div>
<script>
{literal}
// TODO no more tours and DCMenu or quick edits :)
//if(SUGAR.util.isTouchScreen()) {
//        setTimeout(resizeHeader,10000);
//}
//
////qe_init function sets listeners to click event on elements of 'quickEdit' class
// if(typeof(DCMenu) !='undefined'){
//    DCMenu.qe_refresh = false;
//    DCMenu.qe_handle;
// }
//function qe_init(){
//
//    //do not process if YUI is undefined
//    if(typeof(YUI)=='undefined' || typeof(DCMenu) == 'undefined'){
//        return;
//    }
//
//
//    //remove all existing listeners.  This will prevent adding multiple listeners per element and firing multiple events per click
//    if(typeof(DCMenu.qe_handle) !='undefined'){
//        DCMenu.qe_handle.detach();
//    }
//
//    //set listeners on click event, and define function to call
//    YUI().use('node', function(Y) {
//        var qe = Y.all('.quickEdit');
//        var refreshDashletID;
//        var refreshListID;
//
//        //store event listener handle for future use, and define function to call on click event
//        DCMenu.qe_handle = qe.on('click', function(e) {
//            //function will flash message, and retrieve data from element to pass on to DC.miniEditView function
//            ajaxStatus.flashStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'),800);
//            e.preventDefault();
//            if(typeof(e.currentTarget.getAttribute('data-dashlet-id'))!='undefined'){
//                refreshDashletID = e.currentTarget.getAttribute('data-dashlet-id');
//            }
//            if(typeof(e.currentTarget.getAttribute('data-list'))!='undefined'){
//                refreshListID = e.currentTarget.getAttribute('data-list');
//            }
//            DCMenu.miniEditView(e.currentTarget.getAttribute('data-module'), e.currentTarget.getAttribute('data-record'),refreshListID,refreshDashletID);
//        });
//
//    });
//}
//
//    qe_init();
//
//
//	SUGAR_callsInProgress++;
//	SUGAR._ajax_hist_loaded = true;
//    if(SUGAR.ajaxUI)
//    	YAHOO.util.Event.onContentReady('ajaxUI-history-field', SUGAR.ajaxUI.firstLoad);
</script>
{/literal}

</body>
</html>
