/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

({
    configUrl: '',

    /**
     * {@inheritdoc}
     */
    initialize: function (options) {
        var fields = options.meta.panels[0].fields;
        this._super('initialize', [options]);
        this._initModel(fields);
        this.config = app.metadata.getModule('KBDocuments', 'config');
        this.model.set(this.config, {silent: true});

        this.configUrl = app.api.buildURL(this.module, 'config', null, {});
        app.api.call('read', this.configUrl, null, {
            success:  _.bind(function (data) {
                if (this.disposed) {
                    return;
                }
                this.config = data;
                this.model.set(this.config, {silent: true});
                this.render();
            }, this)
        });
        this.context.on('button:save_button:click', this.saveClicked, this);
    },

    /**
     * Initialize model according to metadata.
     *
     * @param {Object} opt
     * @private
     */
    _initModel: function (opt) {
        this.model = app.data.createBean('config');
        _.each(opt, function (def, name) {
            var fld = _.isObject(def) ? def.name : name,
                ins = {};
            ins[fld] = '';
            this.model.set(ins);
        }, this);
    },

    /**
     * Handle save.
     */
    saveClicked: function () {
        app.api.call('update', this.configUrl, this.model.changedAttributes(), {
            success:  _.bind(function () {
                if (this.disposed) {
                    return;
                }
                this.context.trigger('config:saved', this.model);
                this.dispose();
            }, this)
        });
    },

    /**
     * {@inheritDoc}
     */
    _dispose: function () {
        this.context.off('button:save_button:click', this.saveClicked, this);
        this._super('_dispose');
    }
})
