/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.ListBottomView
 * @alias SUGAR.App.view.views.BaseListBottomView
 * @extends View.View
 */
({
    events: {
        'click [data-action="show-more"]': 'showMoreRecords'
    },

    initialize: function(options) {
        this._super('initialize', [options]);
        this._initPagination();
    },

    /**
     * Initialize pagination component in order to react the show more link.
     * @private
     */
    _initPagination: function() {
        this.paginationComponent = _.find(this.layout._components, function(component) {
            return _.contains(component.plugins, 'Pagination');
        }, this);
    },

    /**
     * Retrieving the next page records by pagination plugin.
     *
     * Please see the {@link app.plugins.Pagination#getNextPagination}
     * for detail.
     */
    showMoreRecords: function() {
        if (!this.paginationComponent) {
            return;
        }

        this.paginateFetched = false;
        this.render();

        var options = {};
        options.success = _.bind(function() {
            this.layout.trigger('list:paginate:success');
            this.paginateFetched = true;
            this.render();
        }, this);

        this.paginationComponent.getNextPagination(options);
    },

    /**
     * Assign proper label for 'show more' link.
     * Label should be "More <module name>...".
     */
    setShowMoreLabel: function() {
        var model = this.collection.at(0),
            module = model ? model.module : this.context.get('module');
        this.showMoreLabel = app.lang.get('TPL_SHOW_MORE_MODULE', module, {
            module: app.lang.get('LBL_MODULE_NAME', module).toLowerCase(),
            count: this.collection.length,
            offset: this.collection.next_offset >= 0
        });
    },

    /**
     * Reset previous collection handlers and
     * bind the listeners for new collection.
     */
    onCollectionChange: function() {
        var prevCollection = this.context.previous('collection');
        if (prevCollection) {
            prevCollection.off(null, null, this);
        }
        this.collection = this.context.get('collection');
        this.collection.on('add remove reset', this.render, this);
        this.render();
    },

    /**
     * {@inheritDoc}
     *
     * Bind listeners for collection updates.
     * The pagination link synchronizes its visibility with the collection's
     * status.
     */
    bindDataChange: function() {
        this.context.on('change:collection', this.onCollectionChange, this);
        this.collection.on('add remove reset', this.render, this);
        this.before('render', function() {
            this.dataFetched = this.paginateFetched !== false && this.collection.dataFetched;
            var nextOffset = this.collection.next_offset || -1;
            if (this.collection.dataFetched && nextOffset === -1) {
                this._invisible = true;
                this.hide();
                return false;
            }
            this._invisible = false;
            this.show();
            this.setShowMoreLabel();
        }, null, this);
    },

    /**
     * {@inheritDoc}
     *
     * Avoid to be shown if the view is invisible status.
     * Add dashlet placeholder's class in order to handle the custom css style.
     */
    show: function() {
        if (this._invisible) {
            return;
        }
        this._super('show');
        if (!this.paginationComponent) {
            return;
        }
        this.paginationComponent.layout.$el.addClass('pagination');
    },

    /**
     * {@inheritDoc}
     *
     * Remove pagination custom CSS class on dashlet placeholder.
     */
    hide: function() {
        this._super('hide');
        if (!this.paginationComponent) {
            return;
        }
        this.paginationComponent.layout.$el.removeClass('pagination');
    }
})
