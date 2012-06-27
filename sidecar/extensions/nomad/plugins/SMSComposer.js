//
//  SMSComposer.js
//

/**
 * SMS Composer plugin for Cordova
 * window.plugins.SMSComposer
 * 
 * @constructor
 */

/**
 * Cordova plugin, adds SMS sending support
 *
 * **SMSComposer provides:**
 *
 * - Method to send SMS
 * - With or without showing composer
 * - To single or multiple phone numbers
 *
 * **SMSComposer examples**
 *
 *
 *  window.plugins.smsComposer.showSMSComposer('3424221122', 'hello');
 *
 *
 *  window.plugins.smsComposer.showSMSComposer();
 *
 *
 *  window.plugins.smsComposer.showSMSComposer('3424221122,2134463330', 'hello');
 *
 *
 *  window.plugins.smsComposer.showSMSComposerWithCB(myCallback,'3424221122,2134463330', 'hello');
 *
 *
 *  var myCallback = function(result){
 *
 *  if(result == 0)
 *    alert("Cancelled");
 *  else if(result == 1)
 *    alert("Sent");
 *  else if(result == 2)
 *    alert("Failed.");
 *  else if(result == 3)
 *    alert("Not Sent.");
 *  }
 *
 *
 * ** Source **
 *
 * see https://github.com/phonegap/phonegap-plugins/tree/master/iPhone/SMSComposer
 *
 */

function SMSComposer()
{
	this.resultCallback = null;
}

SMSComposer.ComposeResultType =
{
Cancelled:0,
Sent:1,
Failed:2,
NotSent:3
}

SMSComposer.prototype.showSMSComposer = function(toRecipients, body)
{
	
	var args = {};
	
	if(toRecipients)
		args.toRecipients = toRecipients;
	
	if(body)
		args.body = body;
	
	Cordova.exec("SMSComposer.showSMSComposer",args);
}

SMSComposer.prototype.showSMSComposerWithCB = function(cbFunction,toRecipients,body)
{
	this.resultCallback = cbFunction;
	this.showSMSComposer.apply(this,[toRecipients,body]);
}

SMSComposer.prototype._didFinishWithResult = function(res)
{
	this.resultCallback(res);
}

Cordova.addConstructor(function() {
					   
					   if(!window.plugins)	{
					   window.plugins = {};
					   }
					   window.plugins.smsComposer = new SMSComposer();
					   });
