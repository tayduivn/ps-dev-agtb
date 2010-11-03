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

// $Id: RSSDashletScript.tpl,v 1.1 2006/10/11 00:53:31 clint Exp $

*}
{literal}<script>
if(typeof RSSDashlet == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions
	RSSDashlet = function() {
		var _anim;
		var _scrollSpeed = '{/literal}{$scroll_speed}{literal}';
		var _computedSpeed;
		var _computedScale;
		var _durationConst = 2.5;
		var _durationAdditive = 5;
		
	    return {
			animate: function(speed){
					var element = document.getElementById('rss_{/literal}{$id}{literal}');

					if(_computedSpeed != speed){
						_computedSpeed = speed;
					}
					
					_computedScale = (_computedSpeed)/_durationAdditive;
					_computedScale = _computedScale + _durationConst;
						var attributes = {
      						scroll: { to: [YAHOO.util.Dom.get('rss_{/literal}{$id}{literal}').scrollLeft, YAHOO.util.Dom.get('rss_{/literal}{$id}{literal}').scrollHeight+YAHOO.util.Dom.get('rss_{/literal}{$id}{literal}').clientHeight] }
      					};
      				
      				//if(!_anim){
    					_anim = new YAHOO.util.Scroll('rss_{/literal}{$id}{literal}', attributes);
   						_anim.duration = YAHOO.util.Dom.get('rss_{/literal}{$id}{literal}').scrollHeight/_computedScale;
   					//}
					_anim.animate(); 
					_anim.onComplete.subscribe(RSSDashlet.complete);
			},
			stop: function(){		
					_anim.stop();
			},
			complete: function(){

				//if(YAHOO.util.Dom.get('rss_{/literal}{$id}{literal}').scrollTop+YAHOO.util.Dom.get('rss_{/literal}{$id}{literal}').clientHeight >= YAHOO.util.Dom.get('rss_{/literal}{$id}{literal}').scrollHeight){
				//			SUGAR.sugarHome.retrieveDashlet('{/literal}{$id}{literal}');
				//}
				_anim.stop();
			},
			isAnimated: function(){		
					return _anim.isAnimated();
			}
	    };
	}();
}
//YAHOO.util.Event.onAvailable('rss_{/literal}{$id}{literal}', RSSDashlet.animate);
</script>{/literal}