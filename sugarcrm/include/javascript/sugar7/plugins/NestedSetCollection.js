/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function (app) {
    app.events.on("app:init", function () {

        app.plugins.register('NestedSetCollection', ['view'], {
            url: null,
            onAttach: function (component, plugin) {
                this.on('init', function () {
                    this.initCollection();
                }, this);
            },
            setModelAttributes: function (data, options) {
                if (_.isObject(data) && !_.isUndefined(data.children) && _.isNull(this.children)) {
                    this.children = this.collection.clone().reset(data.children.records);
                    data = _.omit(data, "children");
                }

                app.Bean.prototype.set.apply(this, arguments);
            },

            /**
             * Defines the base NestedSet collection and model. This class is considered to be abstract.
             *
             * @type {initCollection}
             * @abstract
             */
            initCollection: function () {
                var NestedSetBean = app.Bean.extend({
                    children: null,
                    set: this.setModelAttributes,
                });

                var NestedSetCollection = app.BeanCollection.extend({
                    model: NestedSetBean,
                    jsonTree: null,
                    root: null,
                    offset: -1,
                    tree: _.bind(this.tree, this),
                    sync: _.bind(this.sync, this)
                });

                this.collection = new NestedSetCollection();
            },

            /**
             * Load a nestedset data collection.
             */
            tree: function (options) {
                var parts = [],
                        url;
                parts.push(app.api.serverUrl);
                parts.push(this.collection.module);
                parts.push(this.collection.root);
                parts.push('tree');
                this.url = parts.join('/');
                this.collection.parse = _.bind(this.parseTree, this);
                this.collection.fetch(options);
            },

            /**
             * This method override BeanCollection.parse method and store collection JSON raw tree data. 
             *
             * @type {parseTree}
             */
            parseTree: function (response, options) {
                this.collection.jsonTree = response;
                return app.BeanCollection.prototype.parse.apply(this, arguments);
            },

            /**
             * This method override sync api to call correct API url. 
             *
             * @type {sync}
             */
            sync: function (method, model, options) {
                var callbacks = app.data.getSyncCallbacks(method, model, options);
                app.api.call(method, this.url, options, callbacks);
            }

        });
    });
})(SUGAR.App);
