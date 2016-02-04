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
 
// $Id: style.js 23344 2007-06-05 20:32:59Z eddy $

YAHOO.util.Event.onContentReady('HideHandle',function()
{
	document.getElementById('HideHandle').onmouseover = function()
	{
	    if(document.getElementById("leftColumn").style.display=='none'){
	        tbButtonMouseOver('HideHandle',75,'',10);
	    }
	}
});