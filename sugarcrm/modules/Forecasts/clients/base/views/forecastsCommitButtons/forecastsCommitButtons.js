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
/**
 * Events Triggered
 *
 * forecasts:commitButtons:disabled
 *      on: context
 *      by: change:selectedUser, change:selectedTimePeriod
 *
 * forecasts:worksheet:saveWorksheet
 *      on:context
 *      by: triggerCommit(), triggerSaveDraft()
 *
 * modal:forecastsTabbedConfig:open - to cause modal.js to pop up
 *      on: layout
 *      by: triggerConfigModal()
 *
 * forecasts:committed:commit
 *      on: context
 *      by: triggerCommit()
 *
 * forecasts:commitButtons:sidebarHidden
 *      on: context
 *      by: triggerRightColumnVisibility()
 */
({

    /**
     * Used to determine whether or not to visibly show the Commit button
     */
    showCommitButton: true,

    /**
     * Used to determine whether or not the Commit button is enabled
     */
    commitButtonEnabled: false,

    /**
     * Used to determine whether the config setting cog button is displayed
     */
    showConfigButton: false,

    /**
     * Used to know which version to save, draft or live
     */
    draft: 0,

    /**
     * Track if the inspector is visible
     */
    inspectorVisible: false,

    /**
     * Track if right panel is visible
     */
    rightPanelVisible: true,

    /**
     * keeps reference to the container of the right panel element
     */
    rightPanelContainerEl: null,

    /**
     * keeps reference to chevrons element
     */
    chevronsEl: null,

    /**
     * Adds event listener to elements
     */
    events: {
        "click a[id=commit_forecast]": "triggerCommit",
        "click a[id=save_draft]": "triggerSaveDraft",
        "click a.drawerTrig": "triggerRightColumnVisibility",
        "click a[id=export]": "triggerExport",
        "click a[id=print]": "triggerPrint"
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.showConfigButton = (_.isUndefined(app.user.getAcls()['Forecasts'].admin));
    },

    /**
     * Clean up any left over bound data to our context
     */
    unbindData: function() {
        if(this.context) this.context.off(null, null, this);
        app.view.View.prototype.unbindData.call(this);
    },

    /**
     * Fires during initialization and if any data changes on this model
     */
    bindDataChange: function() {
        var self = this;
        if(this.context) {
            this.context.on("change:selectedUser", function(context, user) {
                var oldShowButtons = this.showCommitButton;
                this.showCommitButton = self.checkShowCommitButton(user.id);
                // if show buttons has changed, need to re-render
                if(this.showCommitButton != oldShowButtons) {
                    this._render();
                }
            }, this);
            this.context.on("forecasts:worksheet:reloadCommitButton", function() {
                this._render();
            }, this);
            this.context.on("forecasts:worksheetManager:reloadCommitButton", function() {
                self._render();
            }, self);
            //this.context.forecasts.worksheet.on("change", this.showSaveButton, self);
            this.context.on('forecasts:worksheet:dirty', function(model, changed) {
                this.$el.find('#save_draft').removeClass("disabled");
                this.context.trigger("forecasts:commitButtons:enabled");
            }, self);
            this.context.on("forecasts:commitButtons:triggerCommit", this.triggerCommit, self);
            this.context.on("forecasts:commitButtons:triggerSaveDraft", this.triggerSaveDraft, self);
            this.context.on("change:selectedTimePeriod", function() {
                this.context.trigger("forecasts:commitButtons:disabled");
            }, this);
            this.context.on("forecasts:commitButtons:enabled", this.enableCommitButton, this);
            this.context.on("forecasts:commitButtons:disabled", this.disableCommitButton, this);
        }

        this.layout.on('inspectorVisible', function(visible) {
            this.inspectorVisible = visible;
            // if inspectorVisible is changed to true, make sure we change rightPanelVisible
            if(this.inspectorVisible) {
                this.rightPanelVisible = visible;
            }
            this.setChevronsClass();
            this.setRightPanelClass();
        }, this);
    },

    /**
     * Renders the component
     */
    _renderHtml: function(ctx, options) {
        app.view.View.prototype._renderHtml.call(this, ctx, options);
        // grab references to elements the first time this renders
        if(!this.rightPanelContainerEl) {
            this.rightPanelContainerEl = this.$el.parents('#contentflex').find('>div.row-fluid');
            this.chevronsEl = this.$el.find('.drawerTrig i');
        }

        if(this.showCommitButton) {
            if(this.commitButtonEnabled) {
                this.$el.find('a[id=commit_forecast]').removeClass('disabled');
            } else {
                this.$el.find('a[id=commit_forecast]').addClass('disabled');
            }
        }
    },

    /**
     * Event handler to disable/reset the commit/save button
     */
    disableCommitButton: function() {
        var commitbtn = this.$el.find('#commit_forecast');
        var savebtn = this.$el.find('#save_draft');
        commitbtn.addClass("disabled");
        savebtn.addClass("disabled");

        this.commitButtonEnabled = false;
    },

    /**
     * Event handler to disable/reset the commit button
     */
    enableCommitButton: function() {
        var commitbtn = this.$el.find('#commit_forecast');
        commitbtn.removeClass("disabled");
        this.commitButtonEnabled = true;
    },

    /**
     * Sets the flag on the context so forecastsCommitted.js will call commitForecast
     * as long as commit button is not disabled
     */
    triggerCommit: function() {
        var commitbtn = this.$el.find('#commit_forecast');

        if(!commitbtn.hasClass("disabled")){
            this.disableCommitButton();

            this.context.once('forecasts:worksheet:saved', function(){
                // now actually commit the forecast
                this.context.trigger('forecasts:committed:commit');
            }, this);
            this.context.trigger("forecasts:worksheet:saveWorksheet", false);            
    	}        
    },

    /**
     * Handles Save Draft button being clicked
     */
    triggerSaveDraft: function() {
        var savebtn = this.$el.find('#save_draft');

        if(!savebtn.hasClass("disabled")) {
            this.context.trigger("forecasts:worksheet:saveWorksheet", true);
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
    /**
     * Toggle the right Column Visibility
     * @param evt
     */
    triggerRightColumnVisibility: function(evt) {
        evt.preventDefault();

        if(!this.inspectorVisible) {
            // toggle rightPanelVisible boolean
            this.rightPanelVisible = !this.rightPanelVisible;
            this.setChevronsClass();
            this.setRightPanelClass();
        }

        // toggle the "event" to make the chart stop rendering if the sidebar is hidden
        this.context.trigger('forecasts:commitButtons:sidebarHidden', this.$el.find('i').hasClass('icon-double-angle-left'));
    },

    /**
     * Sets the >> and << chevrons to the proper class
     */
    setChevronsClass: function() {
        if(this.rightPanelVisible) {
            // set to open-panel chevron class
            this.chevronsEl.removeClass('icon-double-angle-left').addClass('icon-double-angle-right');
        } else {
            // set to closed-panel chevron class
            this.chevronsEl.removeClass('icon-double-angle-right').addClass('icon-double-angle-left');
        }
    },

    /**
     * Sets the main Forecast div and the right panel div to the proper classes
     */
    setRightPanelClass: function() {
        if(this.rightPanelVisible) {
            // set main div and right panel to open-panel classes
            this.rightPanelContainerEl.find('>div:first').removeClass('span12').addClass('span8');
            this.rightPanelContainerEl.find('>div:last').removeClass('hide').addClass('span4');
        } else {
            // set main div and right panel to closed-panel classes
            this.rightPanelContainerEl.find('>div:first').removeClass('span8').addClass('span12');
            this.rightPanelContainerEl.find('>div:last').removeClass('span4').addClass('hide');
        }
    },

    /**
     * Trigger the export to send csv data
     * @param evt
     */
    triggerExport: function(evt) {
        var savebtn = this.$el.find('#save_draft');
        var url = 'index.php?module=Forecasts&action=';
        url += (this.context.get("currentWorksheet") == 'worksheetmanager') ? 'ExportManagerWorksheet' : 'ExportWorksheet';
        url += '&user_id=' + this.context.get('selectedUser').id;
        url += '&timeperiod_id=' + this.context.get('selectedTimePeriod').id;

        if(savebtn.length > 0 && !savebtn.hasClass("disabled")) {
            if(confirm(app.lang.get("LBL_WORKSHEET_EXPORT_CONFIRM", "Forecasts"))) {
                this.runExport(url);
            }
        }
        else {
            this.runExport(url);
        }
    },

    /**
     * runExport
     * triggers the browser to download the exported file
     * @param url URL to the file to download
     */
    runExport: function(url) {
        var dlFrame = $("#forecastsDlFrame");
        //check to see if we got something back
        if(dlFrame.length == 0) {
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
    triggerPrint: function(evt) {
        window.print();
    }

})
