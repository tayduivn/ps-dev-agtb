/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ('Company') that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ('MSA'), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('FieldDuplicate', ['field'], {
            /**
             * Contains id of {Data.Bean} from which field should be duplicated.
             *
             * @property {String} _duplicateBeanId
             * @protected
             */
            _duplicateBeanId: null,

            /**
             * Setup id of {Data.Bean} from which field should be duplicated.
             *
             * @param {String} modelId Id of model.
             */
            duplicateFromModel: function(modelId) {
                this._duplicateBeanId = modelId;
            },

            /**
             * Handler for `duplicate:field` event triggered on model. Setup id of
             * model from which field should be duplicated.
             *
             * @param {Data.Bean} model Model from which field should be duplicated.
             * @private
             */
            _onFieldDuplicate: function(model) {
                var modelId = (model instanceof Backbone.Model) ? model.get('id') : null;

                this.duplicateFromModel(
                    (this.model && this.model.get('id') === modelId) ? null : modelId
                );

                if (_.isFunction(this.onFieldDuplicate)) {
                    this.onFieldDuplicate.call(this, model);
                }
            },

            /**
             * Bind handlers for field duplication.
             *
             * @param {View.Component} component Component to attach plugin.
             * @param {Object} plugin Object of plugin to attach.
             * @return {void}
             */
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    if (this.model) {
                        this.model.on('change:' + this.name, function() {
                            this._onFieldDuplicate();
                        }, this);
                        this.model.on('duplicate:field', this._onFieldDuplicate, this);
                        this.model.on('duplicate:field:' + this.name, this._onFieldDuplicate, this);
                        this.model.on('data:sync:start', function(method, options) {
                            if (!_.isNull(this._duplicateBeanId) &&
                                (method == 'update' || method == 'create')
                            ) {
                                options.params = options.params || {};
                                options.params[this.name + '_duplicateBeanId'] = this._duplicateBeanId;
                            }
                        }, this);
                    }
                });
            }
        });
    });
})(SUGAR.App);
