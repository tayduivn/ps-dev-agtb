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

({
    /**
     * Events Triggered
     *
     * data:sync:start
     *      on: this
     *      by: initialize()
     *      when: the config model syncs
     *
     *
     */

    /**
     * Saved Labels to use for the Breadcrumbs
     */
    breadCrumbLabels: [],

    initialize: function (options) {
        var modelUrl = app.api.buildURL("Forecasts", "config"),
            modelSync = function(method, model, options) {
                this.trigger("data:sync:start", method, model, options);
                var url = _.isFunction(model.url) ? model.url() : model.url;
                return app.api.call(method, url, model, options);
            },
            settingsModel = this._getConfigModel(options, modelUrl, modelSync);

        settingsModel.fetch();

        options.context.set("model", settingsModel);

        app.view.Layout.prototype.initialize.call(this, options);
    },

    /**
     * Gets a config model for the config settings dialog.
     *
     * If we're using this layout from inside the Forecasts module and forecasts already has a config model, config
     * will use that config model as our current context so we're updating a clone of the same model.
     * The clone facilitates not saving to a "live" model, so if a user hits cancel, the values will go back to the
     * correct setting the next time the admin panel is accessed.
     *
     * If we're not coming in from the Forecasts module (e.g. Admin)
     * creates a new model and config will use that to change/save
     * @return {Object} the model for config
     */
    _getConfigModel: function(options, syncUrl, syncFunction) {
        var SettingsModel = Backbone.Model.extend({
            url: syncUrl,
            sync: syncFunction,
            // Fetch the model from the server. If the server's representation of the
            // model differs from its current attributes, they will be overriden,
            // triggering a `"change"` event.
            fetch: function (options) {
                options = options ? _.clone(options) : {};
                if (options.parse === void 0) options.parse = true;
                var model = this;
                var success = options.success;
                options.success = function (resp) {
                    if (!model.set(model.parse(resp, options), options)) return false;
                    if (success) success(model, resp, options);
                    model.trigger('sync', model, resp, options);
                };
                var error = options.error;
                options.error = function (resp) {
                    if (error) error(model, resp, options);
                    model.trigger('error', model, resp, options);
                };
                return this.sync('read', this, options);
            },
            // Set a hash of model attributes, and sync the model to the server.
            // If the server returns an attributes hash that differs, the model's
            // state will be `set` again.
            save: function(key, val, options) {
                var attrs, method, xhr, attributes = this.attributes;

                // Handle both `"key", value` and `{key: value}` -style arguments.
                if (key == null || typeof key === 'object') {
                    attrs = key;
                    options = val;
                } else {
                    (attrs = {})[key] = val;
                }

                // If we're not waiting and attributes exist, save acts as `set(attr).save(null, opts)`.
                if (attrs && (!options || !options.wait) && !this.set(attrs, options)) return false;

                options = _.extend({validate: true}, options);

                // Do not persist invalid models.
                if (!this._validate(attrs, options)) return false;

                // Set temporary attributes if `{wait: true}`.
                if (attrs && options.wait) {
                    this.attributes = _.extend({}, attributes, attrs);
                }

                // After a successful server-side save, the client is (optionally)
                // updated with the server-side state.
                if (options.parse === void 0) options.parse = true;
                var model = this;
                var success = options.success;
                options.success = function(resp) {
                    // Ensure attributes are restored during synchronous saves.
                    model.attributes = attributes;
                    var serverAttrs = model.parse(resp, options);
                    if (options.wait) serverAttrs = _.extend(attrs || {}, serverAttrs);
                    if (_.isObject(serverAttrs) && !model.set(serverAttrs, options)) {
                        return false;
                    }
                    if (success) success(model, resp, options);
                    model.trigger('sync', model, resp, options);
                };

                method = this.isNew() ? 'create' : (options.patch ? 'patch' : 'update');
                if (method === 'patch') options.attrs = attrs;
                xhr = this.sync(method, this, options);

                // Restore attributes.
                if (attrs && options.wait) this.attributes = attributes;

                return xhr;
            }
        });

        return new SettingsModel();
    },

    /**
     * Register a new breadcrumb label
     *
     * @param {string} label
     */
    registerBreadCrumbLabel : function(label) {
        var labelObj = {
                'index': this.breadCrumbLabels.length,
                'label': label
            },
            found = false;
        _.each(this.breadCrumbLabels, function(crumb) {
            if(crumb.label == label) {
                found = true;
            }
        })
        if(!found) {
            this.breadCrumbLabels.push(labelObj);
        }
    },

    /**
     * Get the current registered breadcrumb labels
     *
     * @return {*}
     */
    getBreadCrumbLabels : function(){
        return this.breadCrumbLabels;
    }

})
