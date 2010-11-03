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

// $Id: RSSDashletOptions.tpl,v 1.1 2006/10/11 00:53:31 clint Exp $

*}
<script type="text/javascript" src="include/javascript/yui/slider.js?s={$sugar_version}&c={$js_custom_version}"></script>
{literal}
<style type="text/css">
.horizHandleStart { 
    position:absolute; 
    left: -7px; /* the default position is the center of the bg */
    top: 8px;  /* force the image down a bit */
    cursor:default;
    width:18px; 
    height:18px; 
}

#horizWrapper {position:relative; margin-left:0px;width:110px;float:left;}
#horizBGDiv {position:relative; top:0px; background:url({/literal}{$images_dir}{literal}/horizBg.png) no-repeat; height:26px; width:110px;zindex:20 }
#horizValueDiv { position:relative; top: -15px; left:120px; } 
</style>
{/literal}
<div style='width: 500px'>
<form name='configure_{$id}' action="index.php" method="post" onSubmit='return SUGAR.dashlets.postForm("configure_{$id}", SUGAR.sugarHome.uncoverPage);'>
<input type='hidden' name='id' value='{$id}'>
<input type='hidden' name='module' value='Home'>
<input type='hidden' name='action' value='ConfigureDashlet'>
<input type='hidden' name='to_pdf' value='true'>
<input type='hidden' name='configure' value='true'>
<table width="400" cellpadding="0" cellspacing="0" border="0" class="tabForm" align="center">
<tr>
    <td valign='top' nowrap class='dataLabel'>{$titleLbl}</td>
    <td valign='top' class='dataField'>
    	<input class="text" name="title" size='20' value='{$title}'>
    </td>
</tr>
{section name=index loop=$rss_urls start=0 loop=5}
<tr>
    <td valign='top' nowrap class='dataLabel'>{$rssUrlLbl}</td>
    <td valign='top' class='dataField'>
    	<input class="text" name="rss_urls[]" size='20' value='{$rss_urls[index]}'>
    </td>
</tr>
{/section}
<tr>
    <td valign='top' nowrap class='dataLabel'>{$autoScrollLbl}</td>
    <td valign='top' class='dataField'>
    	 <input type='checkbox' {if $auto_scroll == 'true'}checked{/if} name='auto_scroll' value='true'>
    </td>
</tr>
<tr>
<td valign='top' nowrap class='dataLabel'>{$scrollSpeedLbl}</td>
<td  valign='top' class='dataField'>

<div id="horizWrapper">
             	<div
                 id="horizBGDiv"
                 name="horizBGDiv"
                 tabindex="0" 
                 x2:role="role:slider" 
                 state:valuenow="0" 
                 state:valuemin="0" 
                 state:valuemax="100"
                 title="Horizontal Slider" 
                 onkeydown="return handleHorizSliderKey(this, YAHOO.util.Event.getEvent(event))" 
                 onkeypress="YAHOO.util.Event.preventDefault(YAHOO.util.Event.getEvent(event))" >
               		<div id="horizHandleDiv" class="horizHandleStart"><img alt="" src="{$images_dir}/horizSlider.png" /></div> 
              </div> 
   			  <div id="horizValueDiv">
                <input name="horizVal" id="horizVal" type="text" size="4" maxlength="4" readonly value='{$scroll_speed}'/>
              </div>   
</div>
              </td></tr>
<tr>
    <td valign='top' nowrap class='dataLabel'>{$heightLbl}</td>
    <td valign='top' class='dataField'>
    	<input class="text" name="height" size='3' value='{$height}'>
    </td>
</tr>
<tr>
    <td align="right" colspan="2">
        <input type='submit' class='button' value='{$saveLbl}'>
   	</td>
</tr>
</table>
</form>

</div>

{literal}<script type="text/javascript">
//<![CDATA[
	function handleHorizSliderKey(slider, ev) {
	// var valueNow = parseInt(slider.getAttributeNS("http://www.w3.org/2005/07/aaa", "valuenow"), 10);

	var valueNow = horizontalSlider.getValue();

	// var valueMin = parseInt(slider.getAttributeNS("http://www.w3.org/2005/07/aaa", "valuemin"), 10);
	// var valueMax = parseInt(slider.getAttributeNS("http://www.w3.org/2005/07/aaa", "valuemax"), 10);

	var valueMin = horizontalSlider.thumb.rightConstraint;
	var valueMax = horizontalSlider.thumb.leftConstraint;

	var delta = 0;

	var kc = ev.keyCode;

	

	if (kc == YAHOO.util.Key.DOM_VK_LEFT) {
		delta = -25;
	} else if (kc == YAHOO.util.Key.DOM_VK_RIGHT) {
		delta = 25;
	} else if (kc == YAHOO.util.Key.DOM_VK_HOME) {
		delta = -( valueNow - valueMin );
	} else if (kc == YAHOO.util.Key.DOM_VK_END) {
		delta = valueMax - valueNow;
	} else {
		return true;
	}
	
	valueNow += delta;

	horizontalSlider.setValue(valueNow, true);

    /*if (slider.setAttributeNS) {
	    slider.setAttributeNS("http://www.w3.org/2005/07/aaa", 
                              "valuenow", 
                              valueNow);
    }*/
	
	// displaySlider(slider);

    YAHOO.util.Event.stopEvent(ev);
	return false;
}

	var horizontalSlider;
	var firstTime = true;

	function initslider() {
		    
        //////////////////////////////////////////////////////////////////

		horizontalSlider = YAHOO.widget.Slider.getHorizSlider("horizBGDiv", 
                           "horizHandleDiv", 0, 100);

		horizontalSlider.onChange = function(offsetFromStart) {
			if(!firstTime){
				document.getElementById("horizHandleDiv").style.top = "8";
				document.getElementById("horizVal").value = offsetFromStart;
				document.getElementById("horizBGDiv").title = 
                        "Horizontal Slider, value = " + offsetFromStart;
             }else{
             	horizontalSlider.setValue({/literal}{$scroll_speed}{literal});
             }
             firstTime = false;
		};

        horizontalSlider.onSlideStart = function() {
            //alert("slidestart");
           
        };

        horizontalSlider.onSlideEnd = function() {
            // alert("slideend");
           
        };
	}

	function updateHoriz(val) {
        var fld = document.getElementById("horizVal");
		var v = parseFloat(fld.value, 10);
		if ( isNaN(v) ) v = 0;
		horizontalSlider.setValue(Math.round(val));
        var newVal = horizontalSlider.getValue();
        if (v != newVal) {
            fld.value = newVal;
        }
		return false;
	}

	initslider();
	
 //]]>
</script>{/literal}