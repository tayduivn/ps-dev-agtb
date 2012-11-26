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
        this._showModal();
        // initialize the alerts again.
        app.alert.init();
        return this;
    },

    _showModal:function () {
        var self = this;

        if (app.user.getAcls()['Forecasts'].admin == "yes") {
            // begin building params to pass to modal
            var params = {
                title:app.lang.get("LBL_FORECASTS_CONFIG_TITLE", "Forecasts"),
                span:10,
                before:{
                    hide:self.checkSettingsAndRedirect
                },
                components: [
                    { layout: (this.context.forecasts.config.get('is_setup') == 1) ? "tabbedConfig" : "wizardConfig" }
                ]
            };
            // callback has to be a function returning the checkSettingsAndRedirect function
            // to maintain the proper context otherwise from modal, "this" is the Window
            var callback = function () {
                return self.checkSettingsAndRedirect
            };
            this.trigger("modal:forecastsConfig:open", params, callback);
        } else {
            var alert = app.alert.show('no_access_error', {
                    level:'error',
                    messages:app.lang.get("LBL_FORECASTS_CONFIG_USER_SPLASH", "Forecasts"),
                    title:app.lang.get("LBL_FORECASTS_CONFIG_TITLE", "Forecasts")}
            );
            alert.getCloseSelector().on('click', function () {
                return self.checkSettingsAndRedirect();
            })
        }

    },

    /**
     * Checks the is_setup config setting and determines where to send the user
     */
    checkSettingsAndRedirect:function () {
        if (!this.context.forecasts.config.get('is_setup')) {
            // this should only ever happen on the wizard view
            window.location = 'index.php?module=Home';
        } else if (app.metadata.getModule('Forecasts').config.is_setup == 1 && this.context.forecasts.get('saveClicked') == false) {
            // this should only ever happen on the tabbed view when cancel is clicked
            window.location.hash = '#';
        } else {
            // can happen on both views but it's the same methods/messages
            // we have a success save, so we need to call the app.metadata.sync() and then redirect back to the index
            app.alert.show('success', {
                level:'success',
                autoClose:true,
                closeable:false,
                title:app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                messages:[app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_MESSAGE", "Forecasts")]
            });
            // only sync the metadata and then push it back to the main location
            app.metadata.sync(function() {
                window.location.hash = "#"
            });
        }
    }
})
