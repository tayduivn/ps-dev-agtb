/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * This view allows users to add Sugar Apps (external-app) to a Dashboard
 *
 * @class View.Views.Base.ExternalAppDashletView
 * @alias SUGAR.App.view.views.BaseExternalAppDashletView
 * @extends View.Views.Base.ExternalAppView
 */
({
    extendsFrom: 'ExternalAppView',

    plugins: ['Dashlet'],

    className: 'external-app-dashlet',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.services = this._getAvailableServices(options);

        if (!options.meta.config) {
            this.allowApp = this._checkCatalogAccess(options);

            if (!this.allowApp) {
                // if the App is not allowed, set the Catalog Error code
                this.errorCode = 'CAT-404';
            }
        }
        this._super('initialize', [options]);
    },

    /**
     * @inheritdoc
     */
    initDashlet: function() {
        if (this.meta.config) {
            var fields = _.flatten(_.pluck(this.dashletConfig.panels, 'fields'));
            var appDataField = _.find(fields, function(f) {
                return f.name === 'src';
            });
            var options = {};
            this.servicesObj = {};
            _.each(this.services, function(s) {
                options[s.view.src] = s.view.name;
                this.servicesObj[s.view.src] = s.view;
            }, this);
            appDataField.options = options;

            this.settings.on('change:src', function(model) {
                // if the service source changed,
                // update the title and config options
                this.setAppUrlTitle();
            }, this);
        }
    },

    /**
     * @inheritdoc
     */
    render: function() {
        if (this.meta.config) {
            // skip external-app's render and call View's render
            app.view.View.prototype.render.call(this);

            this.setAppUrlTitle();
        } else {
            if (this.allowApp) {
                // if the dashlet is allowed, go ahead and render
                this._super('render');
            } else {
                // else display error that the dashlet is not available
                this.displayError();
            }
        }
    },

    /**
     * Sets the Dashlet title when the URL changes or Dashlet config renders for the first time
     */
    setAppUrlTitle: function() {
        var url = this.settings.get('src');
        if (_.isArray(url)) {
            url = url[0];
        }

        if (!this.currentService || this.currentService !== this.servicesObj[url]) {
            this.currentService = this.servicesObj[url];

            if (this.currentService) {
                // set the dashlet title
                this.settings.set({
                    label: this.currentService.name
                });

                this._render();
            }
        }
    },

    /**
     * @inheritdoc
     */
    loadData: function(onCompleteFn) {
        if (!onCompleteFn) {
            // on first load, no onComplete callback is used
            // so call parent loadData to continue operations
            this._super('loadData');
        } else if (!this.parcelApp) {
            // onCompleteFn exists when user manually clicks "Refresh" button
            // if parcelApp doesn't exist for some reason, try to load it again
            this._onSugarAppLoad();
        }

        if (onCompleteFn && onCompleteFn.complete) {
            // if complete() exists, call it
            onCompleteFn.complete();
        }
    },

    /**
     * Gets any available services that have been added to metadata
     *
     * @param options Init options
     * @return {Array} The array of components available to this view or an empty array
     * @private
     */
    _getAvailableServices: function(options) {
        var layout = app.controller.context.get('layout') === 'records' ? 'list' : 'record';
        var meta = app.metadata.getLayout(options.module, layout + '-dashlet');

        return meta && meta.components ? meta.components : [];
    },

    /**
     * Checks metadata services to make sure Catalog sent over this service to be used
     *
     * @param options Init options
     * @return {boolean} True if the dashlet definition was found in services
     * @private
     */
    _checkCatalogAccess: function(options) {
        var dashletDef = _.find(this.services, function(svc) {
            // find the dashlet service with the same src
            return svc.view.src === options.meta.src;
        }, this);

        return !!dashletDef;
    }
})
