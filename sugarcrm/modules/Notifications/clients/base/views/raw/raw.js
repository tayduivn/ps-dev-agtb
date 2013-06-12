/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

({
    plugins: ['timeago'],

    events: {
        'click span.name': 'toggleName'
    },

    /**
     * {@inheritdoc}
     */
    initialize: function (options) {
        var meta = app.metadata.getView(options.module, 'raw') || {};
        options.meta = _.extend({}, meta, options.meta || {});

        app.view.View.prototype.initialize.call(this, options);

        this._initCollection();
    },

    /**
     * Initialize collection.
     *
     * @return {View.Raw} Instance of this view.
     * @protected
     */
    _initCollection: function () {
        this.collection.options = {params: {order_by: 'date_entered:desc'}};

        this.collection.filterDef = [];
        this.collection.filterDef.push({'$owner': ''});

        // FIXME: the code bellow should be replaced by filter definitions usage
        // on metadata when Filter API has a better support for handling this
        // kind of date operations.
        if (_.isUndefined(this.meta.filter_type)) {
            return this;
        }

        var today = app.date.format(new Date(), 'Y-m-d');
        var filterTypes = {
            'today': {
                'date_entered': {
                    '$between': [today + ' 00:00:00', today + ' 23:59:59']
                }
            },
            'recent': {
                'date_entered': {
                    '$lt': today
                }
            }
        };

        this.collection.filterDef.push(filterTypes[this.meta.filter_type]);

        return this;
    },

    /**
     * {@inheritdoc}
     */
    bindDataChange: function () {
        if (!this.collection) {
            return;
        }

        this.collection.once('reset', function () {
            this._dataFetched = true;
        }, this);

        this.collection.on('reset', this.render, this);
    },

    /**
     * Expand/collapse name column.
     */
    toggleName: function (e) {
        this.$(e.currentTarget).toggleClass('expanded');
    }
})
