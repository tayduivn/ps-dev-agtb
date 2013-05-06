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
        app.view.invoke(this, 'field', 'enum', 'initialize', {args:[options]});
        this.currentView = "default";
        //Check to see if the field is editable
        this.isEditable();
        this.createCTEIconHTML();
    },

    /**
     * override _dispose and make sure custom added select2 and window listeners are off
     * @private
     */
    _dispose: function() {
        this.$("select").off();
        this.$el.find(".select2-input").off();
        $(window).off("click." + this.cid);
        app.view.Component.prototype._dispose.call(this);
    },

    /**
     * Click handler to render the select2 box.
     * 
     * This function disables the click from propagating up, changes the view mode to edit,
     * renders the edit view, opens the dropdown and sets up the close and window.click events.
     */
    clickToEdit: function(e){
        var select = null;
        if(!this.disabled){
            //prevent this click from filtering up to the window.click
            e.preventDefault();
            this.currentView = "edit";
            this.$el.off("click");
            this.def.view = "edit";
            if (!this.disposed) {
                this.render();
            }
            select = this.$("select");
            this.currentVal = select.select2("val");
            select.select2("open");
            select.on("close", _.bind(function(){
                if(_.isEqual(this.currentVal, select.select2("val"))){
                    this.resetField();
                }
            }, this));
            this.$el.find(".select2-input").on('keydown', _.bind(function(e){
                this.onKeyDown(e);
            }, this));

            //custom namespaced window click event to destroy the chosen dropdown on "blur".
            //this is removed in this.resetField
            $(window).on("click." + this.cid, _.bind(function(e){
                if(!_.isEqual(this.cid, $(e.target).attr("cid"))){
                    this.resetField();
                }
            }, this));
        }
    },
    
    /**
     * Change handler
     */
    changed: function(){
        this.resetField();
    },
    
    /**
     * Handle event when key is pressed down (tab)
     *
     * @param evt
     */
    onKeyDown: function(evt) {
        if(evt.which == 9) {
            evt.preventDefault();
            this.$("select").select2("close");
            // tab key pressed, trigger event from context
            this.context.trigger('forecasts:tabKeyPressed', evt.shiftKey, this);
        }
    },
    
    /**
     * Resets field back to initial state.
     * 
     * This resets the field to the detail view, in addition to cleaning up the namespaced window.click handler.
     */
    resetField: function(){
        $(window).off("click." + this.cid);
        this.def.view = "default";
        this.currentView = "default";
        if (!this.disposed) {
            this.render();
        }
        this.$el.on("click", _.bind(function(e){
            this.clickToEdit(e);
        }, this));
    },

    /**
     * Sets up CTE Icon HTML
     * 
     * If the HTML hasn't been set up yet, create it and store it on the DOM.  If it has, simply use it
     */
    createCTEIconHTML: function(){
        var cteIcon = $.data(document.body, "cteIcon"),
            events = this.events || {}
        
        if(_.isUndefined(cteIcon)){
            cteIcon = '<span class="edit-icon"><i class="icon-pencil icon-sm"></i></span>';
            $.data(document.body, "cteIcon", cteIcon);
        }             
        
        //Events
        // if it's not editable, we don't want to try to add the pencil
        this.showCteIcon = _.bind(function() {
            if((this.currentView != "edit") && (!this.disabled)){
                this.$el.find("span").before($(cteIcon));
            }
        }, this);
        
        // if it's not editable, we don't want to try to remove the pencil
        this.hideCteIcon = _.bind(function() {
            if((this.currentView != "edit") && (!this.disabled)){
                this.$el.parent().find(".edit-icon").detach();
            }
        }, this);

        this.events = _.extend(events, {
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
            disableIfSalesStageIs = _.union(
                app.metadata.getModule('Forecasts', 'config').sales_stage_won,
                app.metadata.getModule('Forecasts', 'config').sales_stage_lost
            );
        if(salesStage && _.indexOf(disableIfSalesStageIs, salesStage) != -1) {
            this.disabled = true;
        }
    }
})
