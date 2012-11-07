/**
 * Events Triggered
 *
 * forecasts:commitButtons:disabled
 *      on: context.forecasts
 *      by: change:selectedUser, change:selectedTimePeriod
 *
 * modal:forecastsTabbedConfig:open - to cause modal.js to pop up
 *      on: layout
 *      by: triggerConfigModal()
 */
({

    /**
     * Used to determine whether or not to visibly show the Commit button
     */
    showCommitButton : true,

    /**
     * Used to determine whether or not the Commit button is enabled
     */
    commitButtonEnabled: false,

    /**
     * Used to determine whether the config setting cog button is displayed
     */
    showConfigButton: false,
            
    /**
     * Adds event listener to elements
     */
    events: {
        "click a[id=commit_forecast]" : "triggerCommit",
        "click a[id=save_draft]" : "triggerSaveDraft",
        "click a[name=forecastSettings]" : "triggerConfigModal",
        "click a.drawerTrig" : "triggerRightColumnVisibility",
        "click a[id=export]" : "triggerExport",
        "click a[id=print]" : "triggerPrint"
    },

    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.showConfigButton = (app.user.getAcls()['Forecasts'].admin == "yes");
    },

    /**
     * Fires during initialization and if any data changes on this model
     */
    bindDataChange: function() {
        var self = this;
        if(this.context && this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser", function(context, user) {
                var oldShowButtons = self.showCommitButton;
                self.showCommitButton = self.checkShowCommitButton(user.id);
                // if show buttons has changed, need to re-render
                if(self.showCommitButton != oldShowButtons) {
                    self._render();
                }
            });           
            this.context.forecasts.on("change:reloadCommitButton", function(){
            	self._render();
            }, self);
            this.context.forecasts.worksheet.on("change", this.showSaveButton, self);
            this.context.forecasts.worksheetmanager.on("change", this.showSaveButton, self);
            this.context.forecasts.on("forecasts:forecastcommitbuttons:triggerCommit", this.triggerCommit, self);
            this.context.forecasts.on("change:selectedUser", function(){
            	this.context.forecasts.trigger("forecasts:commitButtons:disabled");
            }, this);
            this.context.forecasts.on("change:selectedTimePeriod", function(){
            	this.context.forecasts.trigger("forecasts:commitButtons:disabled");
            }, this);
            this.context.forecasts.on("forecasts:commitButtons:enabled", this.enableCommitButton, this);
            this.context.forecasts.on("forecasts:commitButtons:disabled", this.disableCommitButton, this);
        }
    },

    /**
     * Renders the component
     */
    _renderHtml : function(ctx, options) {
        app.view.View.prototype._renderHtml.call(this, ctx, options);
        if(this.showCommitButton) {
            if(this.commitButtonEnabled) {
                this.$el.find('a[id=commit_forecast]').removeClass('disabled');
            } else {
                this.$el.find('a[id=commit_forecast]').addClass('disabled');               
            }
        }        
    },
        
    /**
     *	Shows the save button
     * 
     */
    showSaveButton: function(){
    	var self = this;
    	var worksheet = this.context.forecasts[this.context.forecasts.get("currentWorksheet")];
    	
		_.each(worksheet.models, function(model, index){
			var isDirty = model.get("isDirty");
			if(_.isBoolean(isDirty) && isDirty){
				self.enableCommitButton();
				self.$el.find('a[id=save_draft]').removeClass('disabled');
				//if something in the worksheet is dirty, we need to flag the entire worksheet as dirty.
				worksheet.isDirty = true;
			}
		});
    	
    },
    
    /**
     * Event handler to disable/reset the commit/save button
     */
    disableCommitButton: function(){
    	var commitbtn =  this.$el.find('#commit_forecast');
    	var savebtn = this.$el.find('#save_draft');
    	commitbtn.addClass("disabled");
    	savebtn.addClass("disabled");
    	
    	this.commitButtonEnabled = true;
    },
    
    /**
     * Event handler to disable/reset the commit button
     */
    enableCommitButton: function(){
    	var commitbtn =  this.$el.find('#commit_forecast');
    	commitbtn.removeClass("disabled");
    	this.commitButtonEnabled = false;
    },

    /**
     * Sets the flag on the context so forecastsCommitted.js will call commitForecast
     * as long as commit button is not disabled
     */
    triggerCommit: function() {
    	var commitbtn =  this.$el.find('#commit_forecast');
    	var savebtn = this.$el.find('#save_draft');
    	var worksheet = this.context.forecasts[this.context.forecasts.get("currentWorksheet")];
    	var self = this;
    	var modelCount = 0;
		var saveCount = 0;
        if(!commitbtn.hasClass("disabled")){
    		var models = worksheet.models;
    		_.each(models, function(model, index){
    			var isDirty = model.get("isDirty");
    			if(model.get("version") == 0 || (typeof(isDirty) == "boolean" && isDirty)){

    				modelCount++;

        			model.set({
                        draft : 0,
                        isDirty : false,
                        timeperiod_id : self.context.forecasts.get("selectedTimePeriod").id,
                        current_user : app.user.get('id')},
                        {silent:true}
                    );
    				model.url = worksheet.url.split("?")[0] + "/" + model.get("id");
    				model.save({}, {success:function(){
    					saveCount++;
                        //The saveCount === modelCount is being done so that the call to reloadWorksheetFlag is only done after the last
                        //Ajax request is made.  In the future this could perhaps be altered to use the deferred architecture in JQuery
    					if(saveCount === modelCount && self.context.forecasts.get("currentWorksheet") == "worksheetmanager") {
    							self.context.forecasts.set({reloadWorksheetFlag: true});
    					}
    				}});
    				worksheet.isDirty = false;
    			}    			    				
    		});

            savebtn.addClass("disabled");
    		self.context.forecasts.set({commitForecastFlag: true});
    	}        
    },

    /**
     * Triggers the event expected by the modal layout to show the config panels
     */
    triggerConfigModal: function() {
        var params = {
            title: app.lang.get("LBL_FORECASTS_CONFIG_TITLE", "Forecasts"),
            components: [{layout:"tabbedConfig"}],
            span: 10,
            before: {
                hide: function() {
                    // Check to see if we're closing modal via cancel button
                    // We have no event passed here to get which button was clicked
                    if(this.context.forecasts.get('saveClicked')) {
                        // cancel was not clicked, so refresh the page redirecting to the Forecasts module
                        //window.location = 'index.php?module=Forecasts';

                        // call tell the metadata to sync.
                        SUGAR.App.sync({});
                    } else {
                        // reset without a change event in case they click settings again
                        // before refreshing the page
                        this.context.forecasts.set({ saveClicked : false }, {silent:true});
                    }
                }
            }
        };
        if(app.user.getAcls()['Forecasts'].admin == "yes") {
            this.layout.trigger("modal:forecastsTabbedConfig:open", params);
        }
    },

    /**
     * Handles Save Draft button being clicked
     */
    triggerSaveDraft: function() {
    	var savebtn = this.$el.find('#save_draft');
    	if(!savebtn.hasClass("disabled")){
    		var worksheet = this.context.forecasts[this.context.forecasts.get("currentWorksheet")];
    		var self = this;
    		var modelCount = 0;
    		var saveCount = 0;
    		_.each(worksheet.models, function(model, index){
    			var isDirty = model.get("isDirty");
    			if(_.isBoolean(isDirty) && isDirty){
    				modelCount++;
    				model.set({draft: 1}, {silent:true});
    				model.save();
    				model.set({isDirty: false}, {silent:true});
    				worksheet.isDirty = false;
    			}    			    				
    		});

            savebtn.addClass("disabled");
    		this.enableCommitButton();
    	}
    	
    },

    /**
     * returns boolean value indicating whether or not to show the commit button
     */
    checkShowCommitButton: function(id) {
        return app.user.get('id') == id;
    },

    /**
     * Toggle the right Column Visibility
     * @param evt
     */
    triggerRightColumnVisibility : function(evt) {
        // we need to use currentTarget so we always get the a and not any child that was clicked on
        var el = $(evt.currentTarget);
        el.find('i').toggleClass('icon-chevron-right icon-chevron-left');

        // we need to go up and find the parent containg the two rows
        el.parents('#contentflex').find('>div.row-fluid').find('>div:first').toggleClass('span8 span12');
        el.parents('#contentflex').find('>div.row-fluid').find('>div:last').toggleClass('span4 hide');

        // toggle the "event" to make the chart stop rendering if the sidebar is hidden
        this.context.forecasts.set({hiddenSidebar: el.find('i').hasClass('icon-chevron-left')});
    },

    /**
     * Trigger the export to send csv data
     * @param evt
     */
    triggerExport : function(evt) {
        var url = 'index.php?module=Forecasts&action=';
        url += (this.context.forecasts.get("currentWorksheet") == 'worksheetmanager') ?  'ExportManagerWorksheet' : 'ExportWorksheet';
        url += '&user_id=' + this.context.forecasts.get('selectedUser').id;
        url += '&timeperiod_id=' + $("#timeperiod").val();
        
        var dlFrame = $("#forecastsDlFrame");
        //check to see if we got something back
        if(dlFrame.length == 0)
        {
        	//if not, create an element
        	dlFrame = $("<iframe>");
        	dlFrame.attr("id", "forecastsDlFrame");
        	dlFrame.css("display", "none");
        	$("body").append(dlFrame);
        }
        dlFrame.attr("src", url);
    },

    /**
     * Trigger print by calling window.print()
     *
     * @param evt
     */
    triggerPrint : function(evt) {
        window.print();
    }

})