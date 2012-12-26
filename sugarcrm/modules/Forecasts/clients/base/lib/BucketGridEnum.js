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
(function(app) {

    app.view.BucketGridEnum = function (field, view, module) {
        this.field = field;
        this.view = view;
        this.moduleName = module;
        return this.render();
    };

    app.view.BucketGridEnum.prototype.render = function() {
    	
    	var self = this;
           
        this.field.changed = function(){
        	var values = {};
        	var moduleName = self.moduleName;
        	
        	if(self.field.type == "bool"){
        		self.field.value = self.field.unformat();
        		values[self.field.name] = self.field.value;
        	}
        	        	
            values["timeperiod_id"] = self.field.context.forecasts.get("selectedTimePeriod").id;
			values["current_user"] = app.user.get('id');
			values["isDirty"] = true;
			
			//If there is an id, add it to the URL
            if(self.field.model.isNew())
            {
            	self.field.model.url = app.api.buildURL(moduleName, 'create');
            } else {
            	self.field.model.url = app.api.buildURL(moduleName, 'update', {"id":self.field.model.get('id')});
            }
            
            self.field.model.set(values);
        };

        var events = this.field.events || {};
        this.field.events = _.extend(events, {
            'change'  : 'changed'
        });
                
        this.field.delegateEvents();

        return this.field;
    };

})(SUGAR.App);