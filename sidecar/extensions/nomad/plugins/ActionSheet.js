//
//  ActionSheet.js
//
// Created by Olivier Louvignes on 11/27/2011.
// Added Cordova 1.5 support - @RandyMcMillan 2012
//
// Copyright 2011 Olivier Louvignes. All rights reserved.
// MIT Licensed

/**
 * Cordova plugin, adds Action Sheet support
 *
 * **ActionSheet provides:**
 *
 * - Method to show Action Sheet from items array
 * - Callback to get user selection
 *
 * **ActionSheet examples**
 * 
 *  var actionSheet = window.plugins.actionSheet;
 *
 * //Basic with title
 *
 *  actionSheet.create('Title', ['Foo', 'Bar'], function(buttonValue, buttonIndex) {
 *     console.warn('create(), arguments=' + Array.prototype.slice.call(arguments).join(', '));
 *  });
 *
 * // Complex
 *
 *  actionSheet.create(null, ['Add', 'Delete', 'Cancel'], function(buttonValue, buttonIndex) {
 *     console.warn('create(), arguments=' + Array.prototype.slice.call(arguments).join(', '));
 *  }, {destructiveButtonIndex: 1, cancelButtonIndex: 2});
 *
 *
 * ** Source **
 * 
 * see https://github.com/phonegap/phonegap-plugins/tree/master/iOS/ActionSheet
 *
 */


function ActionSheet() {}

ActionSheet.prototype.create = function(title, items, callback, options) {
	if(!options) options = {};
	var scope = options.scope || null;
	delete options.scope;

	var service = 'ActionSheet',
		action = 'create',
		callbackId = service + (cordova.callbackId + 1);

	var config = {
		title : title || '',
		items : items || ['Cancel'],
		style : options.style || 'default',
		destructiveButtonIndex : options.destructiveButtonIndex || undefined,
		cancelButtonIndex : options.cancelButtonIndex || undefined
	};

	var _callback = function(result) {
		var buttonValue = false, // value for cancelButton
			buttonIndex = result.buttonIndex;
		if(!config.cancelButtonIndex || buttonIndex != config.cancelButtonIndex) {
			buttonValue = config.items[buttonIndex];
		}
		callback.call(scope, buttonValue, buttonIndex);
	};

	return cordova.exec(_callback, _callback, service, action, [config]);

};

cordova.addConstructor(function() {
	if(!window.plugins) window.plugins = {};
	window.plugins.actionSheet = new ActionSheet();
});