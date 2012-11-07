/**
 * Events Triggered
 *
 * modal:forecastsWizardConfig:open - to cause modal.js to pop up
 *      on: this
 *      by: _showConfigModal()
 */
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