/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

(function (app) {

    app.view.layouts.ForecastsEmptyLayout = app.view.layouts.ForecastsLayout.extend({

        initialize:function (options) {
            options.context = _.extend(options.context, this.initializeAllModels(options.context));
            options.context.forecasts = {};

            // Initialize the config model
            var ConfigModel = Backbone.Model.extend({
                url: app.api.buildURL("Forecasts", "config"),
                sync: function(method, model, options) {
                    var url = _.isFunction(model.url) ? model.url() : model.url;
                    return app.api.call(method, url, model, options);
                },
                // include metadata from config into the config model by default
                defaults: app.metadata.getModule('Forecasts').config
            });
            options.context.forecasts.config = new ConfigModel();

            app.view.Layout.prototype.initialize.call(this, options);
        },

        /**
         * Dropping in to _render to insert some code to display the config wizard for a user's first run on forecasts.  The render process itself is unchanged.
         *
         * @return {*}
         * @private
         */
        _render: function () {
            app.view.Layout.prototype._render.call(this);
            if(this.context.forecasts.config.get('is_setup') == 1) {
                window.location.hash = "";
            } else {
                this._showConfigModal();
            }
            // initialize the alerts again.
            app.alert.init();
            return this;
        },

        _showConfigModal: function() {
            var self = this;

            // begin building params to pass to modal
            var params = {
                title : app.lang.get("LBL_FORECASTS_CONFIG_TITLE", "Forecasts"),
                span: 10,
                before: {
                    hide : self.checkSettingsAndRedirect
                }
            };

            if(app.user.getAcls()['Forecasts'].admin == "yes") {
                params.components = [{layout:"forecastsWizardConfig"}];
            } else {
                params.message = app.lang.get("LBL_FORECASTS_CONFIG_USER_SPLASH", "Forecasts");
            }

            // callback has to be a function returning the checkSettingsAndRedirect function
            // to maintain the proper context otherwise from modal, "this" is the Window
            var callback = function() { return self.checkSettingsAndRedirect };
            this.trigger("modal:forecastsWizardConfig:open", params, callback);
        },

        /**
         * Checks the is_setup config setting and determines where to send the user
         */
        checkSettingsAndRedirect: function() {
            if(!this.context.forecasts.config.get('is_setup')) {
                window.location = 'index.php?module=Home';
            } else {
                // we have a success save, so we need to call the app.sync() and then redirect back to the index
                //app.alert.show('loading', {level: 'process', title : 'Loading'});
                app.alert.show('success', {level: 'success', title :'Success:', messages: ['You successfully set up your forecasting module. Please wait while it loads.']})
                app.sync({callback: function() {
                    window.location.hash = "#";
                }});
            }
        }
    });

})(SUGAR.App)