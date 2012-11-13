/**
 * Events Triggered
 *
 * modal:forecastsWizardConfig:open - to cause modal.js to pop up
 *      on: this
 *      by: _showConfigModal()
 */
({

        extendsFrom: "ForecastsIndexLayout",

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
                params.components = [{layout:"wizardConfig"}];
                // callback has to be a function returning the checkSettingsAndRedirect function
                // to maintain the proper context otherwise from modal, "this" is the Window
                var callback = function() { return self.checkSettingsAndRedirect };
                this.trigger("modal:forecastsWizardConfig:open", params, callback);
            } else {
                app.alert.init();
                app.alert.show('no_access_error', {
                        level: 'error',
                        messages: app.lang.get("LBL_FORECASTS_CONFIG_USER_SPLASH", "Forecasts"),
                        title: app.lang.get("LBL_FORECASTS_CONFIG_TITLE", "Forecasts")}
                );
                app.alert.get('no_access_error').getCloseSelector().on('click', function(){
                    return self.checkSettingsAndRedirect();
                })
            }

        },

        /**
         * Checks the is_setup config setting and determines where to send the user
         */
        checkSettingsAndRedirect: function() {
            if(!this.context.forecasts.config.get('is_setup')) {
                window.location = 'index.php?module=Home';
            } else {
                // we have a success save, so we need to call the app.sync() and then redirect back to the index
                app.alert.show('success', {
                    level: 'success',
                    autoClose: true,
                    closeable: false,
                    title : app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                    messages: [app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_MESSAGE", "Forecasts")]
                });
                app.sync({callback: function() {
                    window.location.hash = "#";
                }});
            }
        }
})
