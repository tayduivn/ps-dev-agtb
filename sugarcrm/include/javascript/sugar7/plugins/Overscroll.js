/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
(function($) {
	if (!Modernizr.touch) {
		return;
	}
	// JavaScript Document
  // Declare variables
	var touch_x, touch_y, obj_x, obj_y, speed_x=0, speed_y=0, scrollanim;

	document.addEventListener('touchstart', function(e) {
		clearInterval(scrollanim);
		// Get Touch target
        if(e.target == null || e.target == undefined) {
			return;
        }

		obj_x = e.target
		obj_y = e.target
		// Get the target parent that is scrollable
		while ((window.getComputedStyle(obj_x)['overflow-x'] != "auto" && window.getComputedStyle(obj_x)['overflow-x'] != "scroll") || obj_x.parentNode == null) {
			obj_x = obj_x.parentNode
		}
		while ((window.getComputedStyle(obj_y)['overflow-y'] != "auto" && window.getComputedStyle(obj_y)['overflow-y'] != "auto") || obj_y.parentNode == null) {
			obj_y = obj_y.parentNode
		}
		// Get if no scrollable parents are present set null
		if (obj_x.parentNode == null) obj_x = null;
		if (obj_y.parentNode == null) obj_y = null;

		// Get the touch starting point
		var touch = e.touches[0];
		touch_x = touch.pageX;
		touch_y = touch.pageY;
	}, false);

	document.addEventListener('touchmove', function(e) {
		// Clear animation
		clearInterval(scrollanim);

		// Prevent window scrolling
		e.preventDefault();

		// Scroll according to movement
		var touch = e.touches[0];
		obj_x.scrollLeft = obj_x.scrollLeft - (touch.pageX - touch_x)
		obj_y.scrollTop = obj_y.scrollTop - (touch.pageY - touch_y)

		// Set speed speed
		speed_x = (touch.pageX - touch_x)
		speed_y = (touch.pageY - touch_y)

		// Set new positon
		touch_x = touch.pageX;
		touch_y = touch.pageY;
	}, false);

	// Add a final animation as in iOS
	document.addEventListener('touchend', function(e) {
		// Clear previous animations
		clearInterval(scrollanim);

		// Animate
		scrollanim = setInterval(function() {
			obj_x.scrollLeft = obj_x.scrollLeft - speed_x
			obj_y.scrollTop = obj_y.scrollTop - speed_y
			// Decelerate
			speed_x = speed_x * 0.9;
			speed_y = speed_y * 0.9;

			// Stop animation at the end
			if (speed_x < 1 && speed_x > -1 && speed_y < 1 && speed_y > -1) clearInterval(scrollanim)
		},15)

	}, false);
})(jQuery);
