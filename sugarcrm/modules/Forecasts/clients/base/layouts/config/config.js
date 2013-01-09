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
 * modal:forecastsWizardConfig:open - to cause modal.js to pop up
 *      on: this
 *      by: _showConfigModal()
 */
({

    extendsFrom:"ForecastsIndexLayout",

    initialize:function (options) {
        // If is_setup == 1 and users come back to config, the context.forecasts will already be here
        // so only make this new config mode there is no forecasts object on the context
        if(_.isUndefined(options.context.forecasts)) {
            options.context = _.extend(options.context, this.initializeAllModels(options.context));
            options.context.forecasts = new Backbone.Model({'saveClicked' : false});

            // Initialize the config model
            var ConfigModel = Backbone.Model.extend({
                url:app.api.buildURL("Forecasts", "config"),
                sync:function (method, model, options) {
                    var url = _.isFunction(model.url) ? model.url() : model.url;
                    return app.api.call(method, url, model, options);
                },
                // include metadata from config into the config model by default
                defaults:app.metadata.getModule('Forecasts').config
            });
            options.context.forecasts.config = new ConfigModel();
        }

        app.view.Layout.prototype.initialize.call(this, options);
    },

    /**
     * Dropping in to _render to insert some code to display the config wizard for a user's first run on forecasts.  The render process itself is unchanged.
     *
     * @return {*}
     * @private
     */
    _render:function () {
        app.view.Layout.prototype._render.call(this);
        // initialize the alerts again.
        app.alert.init();
        this._showModal();
        return this;
    },

    _showModal:function () {
        var self = this,
            isAdmin = false,
            isSetup = this.context.forecasts.config.get('is_setup');

        // todo-sfa: undo this change once sidecar ACLs are used again
        // on first load, when is_setup == 0, app.initData.selectedUser.admin setting should be used
        // because at that point there is no context.forecasts
        // every other load there will be no app.initData so use the context
        if(!_.isNull(app.initData) && !_.isNull(app.initData.selectedUser)) {
            isAdmin = (app.initData.selectedUser.admin == "yes");
        } else {
            isAdmin = (this.context.forecasts.get('currentUser').admin == "yes");
        }
        if (isAdmin) {
            // begin building params to pass to modal
            var params = {
                title:app.lang.get("LBL_FORECASTS_CONFIG_TITLE", "Forecasts") + ":",
                span:10,
                before:{
                    hide: function() {
                        self.checkSettingsAndRedirect(isSetup,isAdmin);
                    }
                },
                components: [
                    { layout: (this.context.forecasts.config.get('is_setup') == 1) ? "tabbedConfig" : "wizardConfig" }
                ]
            };
            // callback has to be a function returning the checkSettingsAndRedirect function
            // to maintain the proper context otherwise from modal, "this" is the Window
            var callback = function () {
                return self.checkSettingsAndRedirect(isSetup,isAdmin)
            };
            this.trigger("modal:forecastsConfig:open", params, callback);
        } else {
            var alert = app.alert.show('no_access_error', {
                    level:'error',
                    messages:app.lang.get("LBL_FORECASTS_CONFIG_USER_SPLASH", "Forecasts"),
                    title:app.lang.get("LBL_FORECASTS_CONFIG_TITLE", "Forecasts") + ":"}
            );
            alert.getCloseSelector().on('click', function () {
                return self.checkSettingsAndRedirect(isSetup,isAdmin);
            })
        }

    },

    /**
     * Checks the is_setup config setting and determines where to send the user
     * @param isSetup variable to show whether
     * @param isAdmin
     */
    checkSettingsAndRedirect:function (isSetup, isAdmin) {
        var self = this;
        //3 conditions exist
        //1: If the user is not an admin, then the user will be redirected to the main Sugar index
        //2: The user is an admin, but setup has been performed and the cancel has been clicked,
        // then redirect user to Forecasts module
        //3: The user is an admin and setup is complete.  A success message is displayed.  An additional
        // message regarding opportunity setup is displayed if this is the initial setup
        if (!isAdmin) {
            // this should only ever happen on the wizard view and if the user accessing is not an admin
            window.location = 'index.php?module=Home';
        } else if (isSetup == 1 && this.context.forecasts.get('saveClicked') == false) {
            // this should only ever happen on the tabbed view when cancel is clicked
            this.reloadForecasts();
        } else if (this.context.forecasts.get('saveClicked') == false) {
            window.location = 'index.php?module=Forecasts';
        } else {
            // only sync the metadata
            app.metadata.sync();
            // can happen on both views but it's the same methods/messages
            // we have a success save, so we need to call the app.metadata.sync() and then redirect back to the index
            if(!isSetup){
                //issue notice about setting up Opportunities
                var alert = app.alert.show('forecast_opp_notice', {
                    level:'confirmation',
                    showCancel:false,
                    messages: app.lang.get("LBL_FORECASTS_WIZARD_REFRESH_NOTICE", "Forecasts")
                });

                //add alert listener for the close click, in case user clicks the X instead of the confirm button.
                alert.getCloseSelector().on('click', self.displaySuccessAndReload);
            } else {
                this.displaySuccessAndReload();
            }
        }
    },

    /**
     * Displays an alert  and reloads the page
     */
    displaySuccessAndReload:function () {
        var alert = app.alert.show('success', {
            level:'success',
            autoClose:true,
            closeable:true,
            onAutoClose: function() {
                window.location.hash = "#";
            },
            title:app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
            messages:[app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_MESSAGE", "Forecasts")]
        });

        alert.getCloseSelector().on('click', function() {
            window.location.hash = "#";
        });
    }
})
