//FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    /**
     * Attach a Change event to the field
     */
    events: { 'change' : 'bucketsChanged' },   
    
    /**
     * flag for if the field should render disabled
     */
    disabled: false,
    
    /**
     * Language string value of the data in the view.
     */
    langValue: "",
    
    /**
     * Current view type this field is rendered.
     * 
     * This is needed because this.def.view is shared across all instances of the view.
     */
    currentView: "",
    
    /**
     * Initialize
     */
    initialize: function(options){
        app.view.Field.prototype.initialize.call(this, options);
        var self = this,
            forecastRanges = self.context.forecasts.config.get("forecast_ranges");
          
        //Check to see if you're a manager on someone else's sheet, disable changes
        if(self.context.forecasts.get("selectedUser")["id"] != app.user.id){
            self.disabled = true;
        }
        //show_binary, show_buckets, show_n_buckets logic
        if(forecastRanges == "show_binary"){
            //If we're in binary mode
            self.def.view = "bool";
            self.currentView = "bool";
            self.format = function(value){
                return value == "include";
            };
            self.unformat = function(value){
                return self.$el.find(".checkbox").prop('checked') ? "include" : "exclude";
            };
        }
        else if(forecastRanges == "show_buckets"){
            self.def.view = "default";
            self.currentView = "bool";
            self.getLanguageValue();            
            //create buckets, but only if we are on our sheet.
            if(!self.disabled){                
                self.createBuckets();
                self.createCTEIconHTML();
            }            
        }
    },
    
    /**
     * Render Field
     */
    _render:function () {
        var self = this;
        app.view.Field.prototype._render.call(this);
               
        /* If we are on our own sheet, and need to show the dropdown, init things
         * and disable events
         */
        if(!self.disabled && self.currentView == "enum"){
            self.$el.off("click");            
                        
            //custom namespaced window click event to destroy the chosen dropdown on "blur"
            $(window).on("click." + self.model.get("id"), function(e){
                if(!_.isEqual(self.model.get("id"), $(e.target).attr("itemid"))){
                    self.resetBucket();
                }
            });
            
           //custom click handler for the dropdown to set things up for the global click to not fire
            self.$el.on("click", function(e){
                $(e.target).attr("itemid", self.model.get("id"));
            });
                        
            self.$el.off("mouseenter");
            self.$el.off("mouseleave");            
            self.$el.find("option[value=" + self.value + "]").attr("selected", "selected");
            self.$el.find("select").chosen();
        }
    },
    
    /**
     * Change handler for the buckets field
     */
    bucketsChanged: function(){
        var self = this,
            values = {},
            moduleName = self.moduleName;
        
        if(self.currentView == "bool"){
            self.value = self.unformat();
            values[self.def.name] = self.value;
        }
        else if(self.currentView == "enum"){
            self.value = self.$el.find("select")[0].value;
            values[self.def.name] = self.value;
        }
                  
        values["timeperiod_id"] = self.context.forecasts.get("selectedTimePeriod").id;
        values["current_user"] = app.user.get('id');
        values["isDirty"] = true;
        
        //If there is an id, add it to the URL
        if(self.model.isNew())
        {
            self.model.url = app.api.buildURL(moduleName, 'create');
        } else {
            self.model.url = app.api.buildURL(moduleName, 'update', {"id":self.model.get('id')});
        }
        
        self.model.set(values);
        if(self.currentView == "enum"){
            self.resetBucket();
        }
    },
    
    /**
     * Creates the HTML for the bucket selectors
     * 
     * This function is used to create the select tag for the buckets.  For performance reasons, we only want
     * to iterate over the option list once, so we do that here and store it as a jQuery data element on the Body tag.
     * Also, we check to make sure this hasn't already been done (so we don't do it again, of course).
     */
    createBuckets: function(){
        var self = this;
        self.buckets = $.data(document.body, "buckets");
        
        if(_.isUndefined(self.buckets)){
            var options = app.lang.getAppListStrings(this.def.options) || 'commit_stage_dom';
            self.buckets =  "<select data-placeholder=' ' name='" + self.name + "' style='width: 100px;'>";
            self.buckets +=     "<option value='' selected></option>";
            _.each(options, function(item, key){
                self.buckets += "<option value='" + key + "'>" + item + "</options>"
            });
            self.buckets += "</select>";
            $.data(document.body, "buckets", self.buckets);
        }
    },
    
    /**
     * Sets up CTE Icon HTML
     * 
     * If the HTML hasn't been set up yet, create it and store it on the DOM.  If it has, simply use it
     */
    createCTEIconHTML: function(){
        var self = this,
            cteIcon = $.data(document.body, "cteIcon"),
            events = self.events || {},
            sales_stage = self.model.get("sales_stage");
        
        if(_.isUndefined(cteIcon)){
            cteIcon = '<span class="edit-icon"><i class="icon-pencil icon-sm"></i></span>';
            $.data(document.body, "cteIcon", cteIcon);
        }             
        
        //Events
        /* if it's not a bucket, and sales stage is not "Closed Lost", we don't want to add the pencil
         * (Closed Lost IS the key in the language file btw)
         */
        self.showCteIcon = function() {
            if((self.currentView != "enum") && (sales_stage != "Closed Lost")){
                self.$el.find("span").before($(cteIcon));
            }
        };
        
        /* if it's not a bucket, and sales stage is not "Closed Lost", we don't want to try to remove the pencil
         * (Closed Lost IS the key in the language file btw)
         */
        self.hideCteIcon = function() {
            if((self.currentView != "enum") && (sales_stage != "Closed Lost")){
                self.$el.parent().find(".edit-icon").detach();
            }
        };
        
        self.events = _.extend(events, {
            'mouseenter': 'showCteIcon',
            'mouseleave': 'hideCteIcon',
            'click'     : 'clickToEdit'
        });            
    },
    
    /**
     * Gets proper value of the item out of the language file.
     * 
     * If we are in buckets mode and are on a non-editable sheet, we need to display the proper value of this
     * field as determined by the language file.  This function sets the proper key in the field for the hbt to pick it up and
     * display it.
     */
    getLanguageValue: function(){
        var options = app.lang.getAppListStrings(this.def.options) || 'commit_stage_dom';
        this.langValue = options[this.model.get(this.def.name)]; 
    },
    
    /**
     * Click to edit handler
     * 
     * Handles the click to make the field editable.
     */
    clickToEdit: function(e){
        var self = this,
            sales_stage = self.model.get("sales_stage");
        if(sales_stage != "Closed Lost"){
            $(e.target).attr("itemid", self.model.get("id"));
            self.def.view = "enum";
            self.currentView = "enum";
            self.render();
        }
    },
    
    /**
     * Removes chosen dropdown from unfocused field
     */
    resetBucket: function(){
        var self = this;
        
        //remove custom click handler
        $(window).off("click." + self.model.get("id"));
        self.$el.off("click");
        self.def.view = "default";
        self.currentView = "default";
        self.getLanguageValue();
        self.delegateEvents();
        self.render();        
    }
})
