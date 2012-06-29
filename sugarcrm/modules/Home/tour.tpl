<div id="tourStart">
    <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>{$mod.LBL_TOUR_WELCOME}</h3>
    </div>
    
	<div class="modal-body">
		<div style="float: left;"> 
			<div class="well" style="float: left; width: 500px; margin-right: 20px;">
                <object class="movieBox" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" height="281" id="single1" name="single1" width="500">
                    <param name="autostart" value="0">
                    <param name="movie" value="http://d2owqhhe2x3j50.cloudfront.net/media.sugarcrm.com/player.swf" />
                    <param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" />
                    <param name="wmode" value="transparent" />
                    <param name="flashvars" value="file=media.sugarcrm.com/sugar65demos/whatsnewin65_RC3.mp4&amp;streamer=rtmp://s1j4a097o1arx2.cloudfront.net/cfx/st&amp;provider=rtmp&amp;image=include/images/tour/FirstFrame.png&amp;autostart=false" />
                    <embed autostart="false" allowfullscreen="true" allowscriptaccess="always" bgcolor="transparent" class="movieBox" flashvars="file=media.sugarcrm.com/sugar65demos/whatsnewin65_RC3.mp4&amp;streamer=rtmp://s1j4a097o1arx2.cloudfront.net/cfx/st&amp;provider=rtmp&amp;image=include/images/tour/FirstFrame.png&amp;autostart=false" height="281" id="single2" name="single2" src="http://d2owqhhe2x3j50.cloudfront.net/media.sugarcrm.com/player.swf" width="500" wmode="transparent">
                    </embed>
                </object>
                <div class="caption">{$mod.LBL_TOUR_WATCH}</div>
			</div>
			<div style="float: left; width: 300px;" >
				{$mod.LBL_TOUR_FEATURES}
				<p>{$mod.LBL_TOUR_VISIT} <a href="javascript:void window.open('http://support.sugarcrm.com/02_Documentation/01_Sugar_Editions/{$APP.documentation.$sugarFlavor}')">{$mod.LNK_TOUR_DOCUMENTATION}</a>.</p>
			</div>
		</div>
	</div>
    <div class="clear"></div>
    
    <div class="modal-footer">
    <a href="#" class="btn btn-primary">{$APP.LBL_TOUR_TAKE_TOUR}</a>
    <a href="#" class="btn">{$APP.LBL_TOUR_SKIP}</a>
    </div>
</div>
<div id="tourEnd" style="display: none;">
    <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3><i class="icon-ok icon-md"></i> {$mod.LBL_TOUR_DONE}</h3>
    </div>
    
	<div class="modal-body">
		<div style="float: left;"> 
			<div style="float: left; width: 290px; margin-right: 40px;">
			<p>
			{$mod.LBL_TOUR_REFERENCE_1} <a href="javascript:void window.open('http://support.sugarcrm.com/02_Documentation/01_Sugar_Editions/{$APP.documentation.$sugarFlavor}')">{$mod.LNK_TOUR_DOCUMENTATION}</a> {$mod.LBL_TOUR_REFERENCE_2}
<br>
				<i class="icon-arrow-right icon-lg" style="float: right; position: relative; right: -72px; top: -26px;"></i>
			</p>
			</div>
			<div style="float: left">
				<img src="include/images/tour/profile_link.png" width="168" height="247">
			</div>
		</div>
	</div>
    <div class="clear"></div>
    
    <div class="modal-footer">
    <a href="#" class="btn btn-primary">Close</a>
    </div>
</div>