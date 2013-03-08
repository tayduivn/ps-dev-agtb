////FILE SUGARCRM flav=pro ONLY
///********************************************************************************
// *The contents of this file are subject to the SugarCRM Professional End User License Agreement
// *("License") which can be viewed at http://www.sugarcrm.com/EULA.
// *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
// *not use this file except in compliance with the License. Under the terms of the license, You
// *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
// *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
// *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
// *of a third party.  Use of the Software may be subject to applicable fees and any use of the
// *Software without first paying applicable fees is strictly prohibited.  You do not have the
// *right to remove SugarCRM copyrights from the source code or user interface.
// * All copies of the Covered Code must include on each user interface screen:
// * (i) the "Powered by SugarCRM" logo and
// * (ii) the SugarCRM copyright notice
// * in the same form as they appear in the distribution.  See full license for requirements.
// *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
// *to the License for the specific language governing these rights and limitations under the License.
// *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
// ********************************************************************************/
({
    extendsFrom: "EnumField",
    
    /**
     * Attach a Change event to the field
     */
    events: { 'change' : 'changed'},   
    
    /**
     * flag for if the field should render disabled
     */
    disabled: false,
    
    /**
     * Current view type this field is rendered.
     * 
     * This is needed because this.def.view is shared across all instances of the view.
     */
    currentView: "",
    
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
        app.view.fields.EnumField.prototype.initialize.call(this, options);
        var self = this;
        
        self.currentView = "default";
        //Check to see if the field is editable
        self.isEditable();
        self.createCTEIconHTML();  
    },
    
    /**
     * Click handler to render the select2 box.
     * 
     * This function disables the click from propagating up, changes the view mode to edit,
     * renders the edit view, opens the dropdown and sets up the close and window.click events.
     */
    clickToEdit: function(e){
        var self = this,
            select = null;
        if(!self.disabled){
            //prevent this click from filtering up to the window.click
            e.preventDefault();
            self.currentView = "edit";
            self.$el.off("click");
            self.def.view = "edit";
            self.render();
            select = self.$("select");
            self.currentVal = select.select2("val");
            select.select2("open");
            select.on("close", function(){
                if(_.isEqual(self.currentVal, select.select2("val"))){
                    self.resetField();
                }
            });            
            self.$(".select2-input").keydown(function(e){
                self.onKeyDown(e);
            });
            
           
            //custom namespaced window click event to destroy the chosen dropdown on "blur".
            //this is removed in this.resetField
            $(window).on("click." + self.cid, function(e){
                if(!_.isEqual(self.cid, $(e.target).attr("cid"))){
                    self.resetField();
                }
            });
        }
    },
    
    /**
     * Change handler
     */
    changed: function(){
        var self = this;
        self.resetField();
    },
    
    /**
     * Handle event when key is pressed down (tab)
     *
     * @param evt
     */
    onKeyDown: function(evt) {
        var self = this;
        if(evt.which == 9) {
            evt.preventDefault();
            // tab key pressed, trigger event from context
            self.context.trigger('forecasts:tabKeyPressed', evt.shiftKey, self);
        }
    },
    
    /**
     * Resets field back to initial state.
     * 
     * This resets the field to the detail view, in addition to cleaning up the namespaced window.click handler.
     */
    resetField: function(){
        var self = this;
        
        $(window).off("click." + self.cid);
        self.def.view = "default";
        self.currentView = "default";
        self.render();
        self.$el.on("click", function(e){
            self.clickToEdit(e);
        });
    },

    /**
     * Sets up CTE Icon HTML
     * 
     * If the HTML hasn't been set up yet, create it and store it on the DOM.  If it has, simply use it
     */
    createCTEIconHTML: function(){
        var self = this,
            cteIcon = $.data(document.body, "cteIcon"),
            events = self.events || {}
        
        if(_.isUndefined(cteIcon)){
            cteIcon = '<span class="edit-icon"><i class="icon-pencil icon-sm"></i></span>';
            $.data(document.body, "cteIcon", cteIcon);
        }             
        
        //Events
        // if it's not editable, we don't want to try to add the pencil
        self.showCteIcon = function() {
            if((self.currentView != "edit") && (!self.disabled)){
                self.$el.find("span").before($(cteIcon));
            }
        };
        
        // if it's not editable, we don't want to try to remove the pencil
        self.hideCteIcon = function() {
            if((self.currentView != "edit") && (!self.disabled)){
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
     * Utility Method to check if the field is editable
     */
    isEditable: function() {
        this.disabled = false;
        
        //Check to see if you're a manager on someone else's sheet, disable changes
        if(this.context.get("selectedUser")["id"] != app.user.id){
            this.disabled = true;
        }

        var salesStage = this.model.get('sales_stage'),
            disableIfSalesStageIs = ["Closed Won", "Closed Lost"];
        if(salesStage && _.indexOf(disableIfSalesStageIs, salesStage) == -1) {
            this.disabled = true;
        }
    }
})
