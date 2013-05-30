/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

({
    events: {
        'click a[name="cancel_button"]': 'onButtonClicked',
        'click a[name="save_button"]': 'onButtonClicked'
    },

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        /**
         * todo: when backbone.js gets updated to > 0.9.10 this will not be necessary
         * THIS WHOLE SECTION IS A BACKBONE SYNC/FETCH/PARSE HACK
         */
        var model = this.context.get('model');
        model.url = app.api.buildURL("Forecasts", "config");
        model.sync = function(method, model, options) {
            this.trigger("data:sync:start", method, model, options);
            var url = _.isFunction(model.url) ? model.url() : model.url;
            return app.api.call(method, url, model, options);
        };

        model.fetch = function (options) {
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
        };
        // Set a hash of model attributes, and sync the model to the server.
        // If the server returns an attributes hash that differs, the model's
        // state will be `set` again.
        model.save = function(key, val, options) {
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

        // push the model back to the context model
        this.context.set({model: model});
        /**
         * END HACK
         */
    },

    /**
     * Button click handler for cancel and save buttons
     *
     * @param {jQuery.Event} evt click
     */
    onButtonClicked: function(evt) {
        // since the next line is so long, passing it to a variable
        // so it isnt just messily tossed into the switch
        var btnName = $(evt.target).attr('name').slice(0, -7);
        switch(btnName) {
            case 'cancel':
                this.cancelConfig();
                break;
            case 'save':
                this.saveConfig();
                break;
        }
    },

    /**
     * Saves the config model
     */
    saveConfig: function() {
        // Set config settings before saving
        this.context.get('model').set({
            is_setup:true,
            show_forecasts_commit_warnings: true
        });

        this.context.get('model').save({}, {
            // getting the fresh model with correct config settings passed in as the param
            success: _.bind(function(model) {
                // If we're inside a drawer and Forecasts is setup
                if(this.context.get('inDrawer')) {
                    // build an object based on the metadata structure
                    var updatedMetadata = {
                        modules: {
                            Forecasts: {
                                config: model.toJSON()
                            }
                        }
                    };

                    // set Forecasts config to new metadata set in config
                    app.metadata.set(updatedMetadata);

                    // close the drawer and return to Forecasts
                    app.drawer.close(true, model.toJSON());
                } else {
                    // only reason this should not be inDrawer is if user came here from the Admin module or something
                    // where it might not be a drawer.  NOT SURE, but this handles that scenario

                    // only navigate after save api call has returned
                    app.router.navigate('#Forecasts', {trigger: true});
                }

            }, this)
        });
    },

    /**
     * Cancels the config setup process and redirects back
     */
    cancelConfig: function() {
        // If we're inside a drawer and Forecasts is setup
        if(this.context.get('inDrawer') && this.context.get('model').get('is_setup')) {
            // close the drawer
            app.drawer.close();
        } else {
            // otherwise go back to wherever you were before
            app.router.goBack();
        }
    }
})
