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

// $Id$

*}


{literal}<script>
if(typeof FlickrDashlet == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions
 	unescapeHTML = function(t) {
	   var div = document.createElement('div');
	   var text = "";
	   div.innerHTML = t;
	   for (i = 0; i < div.childNodes.length; i++) {
	     text += div.childNodes[i].nodeValue;
	   }
	   return text;
	 }
	FlickrDashlet = function(id) {
		this.id = id;
		this.counter = 0;
		this.feedData = new Array();
		this.images = new Array();
		this.top = 1;
		this.bottom = 2;
		this.animTimeout = null;
		
		YAHOO.util.Dom.setStyle(document.getElementById('flickr_1_' + this.id, 'display'), 'none');
		YAHOO.util.Dom.setStyle(document.getElementById('flickr_2_' + this.id, 'display'), 'none');
	}
	FlickrDashlet.prototype.getFeed = function(tags, obj) {
		loadData = function(data) {
			jsonFlickrFeed = function(a) {
				document.getElementById('flickr_data_' + dashletId).innerHTML = a;
				obj.loadPhotos(a);
			}
			eval(data);
		}
	    SUGAR.dashlets.callMethod(this.id, 'getFeed', 'tags=' + tags, false, loadData);
	}
	FlickrDashlet.prototype.loadPhotos = function(data) {
		this.feedData = data;
		for(i = 0; i < data['items'].length; i++) {
			this.images.push(data['items'][i]);
		}
		this.counter = 0;
		topItem = document.getElementById('flickr_1_' + this.id);
		bottomItem = document.getElementById('flickr_2_' + this.id);
			
		topItem.innerHTML = unescapeHTML(this.images[this.counter]['description']);
		bottomItem.innerHTML = unescapeHTML(this.images[this.counter+1]['description']);
			
		YAHOO.util.Dom.setStyle(bottomItem, 'opacity', 0);
		YAHOO.util.Dom.setStyle(topItem, 'display', '');
		YAHOO.util.Dom.setStyle(bottomItem, 'display', '');
		YAHOO.util.Dom.setStyle(bottomItem, 'position', 'absolute');
		YAHOO.util.Dom.setStyle(bottomItem, 'top', YAHOO.util.Dom.getRegion(topItem).top);
		YAHOO.util.Dom.setStyle(bottomItem, 'left', YAHOO.util.Dom.getRegion(topItem).left);		
//		this.showPhotos(this);
		window.setTimeout('flickrDashlet' + this.id.replace(/-/gi,'') + '.showPhotos(flickrDashlet' + this.id.replace(/-/gi,'') + ')', 5000);
	}
	FlickrDashlet.prototype.showPhotos = function(obj) {
		if(this.counter < this.images.length - 1) {
			topItem = document.getElementById('flickr_' + this.top + '_' + this.id);
			bottomItem = document.getElementById('flickr_' + this.bottom + '_' + this.id);
			entireDashlet = document.getElementById('flickr_entire_' + this.id);

			topItemRegion = YAHOO.util.Dom.getRegion(topItem);
			bottomItemRegion = YAHOO.util.Dom.getRegion(bottomItem);
			
			topItem.innerHTML = unescapeHTML(this.images[this.counter]['description']);
			bottomItem.innerHTML = unescapeHTML(this.images[this.counter+1]['description']);
			YAHOO.util.Dom.setStyle(bottomItem, 'top', topItemRegion.top);
			YAHOO.util.Dom.setStyle(bottomItem, 'left', topItemRegion.left);
			
			maxHeight = (((topItemRegion.bottom - topItemRegion.top) > (bottomItemRegion.bottom - bottomItemRegion.top)) ? (topItemRegion.bottom - topItemRegion.top) : (bottomItemRegion.bottom - bottomItemRegion.top));
			YAHOO.util.Dom.setStyle(entireDashlet, 'height', maxHeight);
			
			topAnim = new YAHOO.util.Anim(topItem, { opacity: {to: 0}, duration: 2 } );
			bottomAnim = new YAHOO.util.Anim(bottomItem, { opacity: {to: 1}, duration: 2 });
			anim = function() {
				topAnim.animate();
				bottomAnim.animate();
			}
			this.animTimeout = window.setTimeout('anim()', 2000);
						
			this.counter++;
			temp = this.bottom;
			this.bottom = this.top;
			this.top = temp;
		}
		else {
			this.counter = 0;
		}
		window.setTimeout('flickrDashlet' + this.id.replace(/-/gi,'') + '.showPhotos(flickrDashlet' + this.id.replace(/-/gi,'') + ')', 5000);
	}
}
</script>

{/literal}