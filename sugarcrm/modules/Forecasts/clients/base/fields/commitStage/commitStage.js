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
     * The select DOM element for this field
     */
    select: "",
    
    /**
     * Current value of the field.
     * 
     * This is used to check the "current" value with the changed value on the close event.  If
     * these match, that means the user clicked the same value in the select box.  If that is the case,
     * the change event never fires (which removes the select2 dropdown).  This is used in the close
     * event handler to reset the field if the current selection is selected.
     */
    currentVal: "",
    
    /**
     * Initialize
     */
    initialize: function(options){
        app.view.Field.prototype.initialize.call(this, options);
        var self = this,
            forecastRanges = self.context.forecasts.config.get("forecast_ranges");
          
        //Check to see if the field is editable
        self.isEditable();
        
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
            self.currentView = "default";
            self.getLanguageValue();
            self.createCTEIconHTML();
            //create buckets, but only if we are on our sheet.
            if(!self.disabled){                
                self.createBuckets();                
            }            
        }
    },
    
    /**
     * Render Field
     */
    _render:function () {
        var self = this,
            select = null;
        app.view.Field.prototype._render.call(this);
               
        /* If we are on our own sheet, and need to show the dropdown, init things
         * and disable events
         */
        if(!self.disabled && self.currentView == "enum"){
            self.$el.off("click");            
                        
            //custom namespaced window click event to destroy the chosen dropdown on "blur".
            //this is removed in this.resetBuckets
            $(window).on("click." + self.cid, function(e){
                if(!_.isEqual(self.cid, $(e.target).attr("cid"))){
                    self.resetBucket();
                }
            });
            
            self.$el.off("mouseenter");
            self.$el.off("mouseleave");
            self.select = self.$el.find("select");
            self.select.select2(!_.isUndefined(self.def.searchBarThreshold)? {minimumResultsForSearch: self.def.searchBarThreshold}:null);
            self.currentVal = self.value;
            self.select.select2("val", self.value);
            self.select.select2("open");
            self.select.on("close", function(){
                if(_.isEqual(self.currentVal, self.select.select2("val"))){
                   self.resetBucket();
               }
           });
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
            self.value = self.select.select2("val");
            values[self.def.name] = self.value;
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
        self.buckets = $.data(document.body, "commitStageBuckets");
        
        if(_.isUndefined(self.buckets)){
            var options = app.lang.getAppListStrings(this.def.options) || 'commit_stage_dom';
            self.buckets =  "<select data-placeholder=' ' name='" + self.name + "' style='width: 100px;'>";
            self.buckets +=     "<option value='' selected></option>";
            _.each(options, function(item, key){
                self.buckets += "<option value='" + key + "'>" + item + "</options>"
            });
            self.buckets += "</select>";
            $.data(document.body, "commitStageBuckets", self.buckets);
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
        // if it's not a bucket, and it's not editable, we don't want to try to add the pencil
        self.showCteIcon = function() {
            if((self.currentView != "enum") && (!self.disabled)){
                self.$el.find("span").before($(cteIcon));
            }
        };
        
        // if it's not a bucket, and it's not editable, we don't want to try to remove the pencil  
        self.hideCteIcon = function() {
            if((self.currentView != "enum") && (!self.disabled)){
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
        if(!self.disabled){
            $(e.target).attr("cid", self.cid);
            self.def.view = "enum";
            self.currentView = "enum";
            e.preventDefault();
            self.render();
        }
    },
    
    /**
     * Removes chosen dropdown from unfocused field
     */
    resetBucket: function(){
        var self = this;
        
        //remove custom click handler
        $(window).off("click." + self.cid);
        self.$el.off("click");
        self.def.view = "default";
        self.currentView = "default";
        self.getLanguageValue();
        self.delegateEvents();
        self.render();        
    },
    
    /**
     * Utility Method to check if the field is editable
     */
    isEditable: function() {
        var self = this,
            sales_stages,
            hasStage = false
            isOwner = true;
        
        if(!_.isUndefined(self.context.forecasts)){
            //Check to see if the sales stage is one of the configured lost or won stages.
            if (!_.isUndefined(self.context.forecasts.config)) {    
                sales_stages = self.context.forecasts.config.get("sales_stage_won").concat(self.context.forecasts.config.get("sales_stage_lost"));
                hasStage = _.contains(sales_stages, self.model.get('sales_stage'));
            }
            
            //Check to see if you're a manager on someone else's sheet, disable changes
            if(self.context.forecasts.get("selectedUser")["id"] != app.user.id){
                isOwner = false;
            }
        }
        
        self.disabled = hasStage || !isOwner; 
    },
})
