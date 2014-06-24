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
 * @class View.Layouts.Base.FilterpanelLayout
 * @alias SUGAR.App.view.layouts.BaseFilterpanelLayout
 * @extends View.Layouts.Base.TogglepanelLayout
 */
({
    extendsFrom: 'TogglepanelLayout',

    /**
     * @inheritDoc
     *
     * Certain options can be set in the filterpanel metadata:
     *     - `auto_apply`: this will determine whether or not to apply the
     *       filter while completing filter rows. This is used mainly because
     *       getRelevantContextList may return the global context and will
     *       filter its collection automatically, and sometimes this is not
     *       desired (e.g. a drawer layout with a filterpanel embedded).
     *
     *     - `stickiness`: this will determine whether or not to save
     *       properties pertaining to filters in localstorage. This is needed
     *       for certain views that have filterpanels, do not require
     *       stickiness and do not want to affect already-stored values in
     *       localstorage (e.g. the filterpanel layout in dashboardconfiguration
     *       shouldn't affect the stickiness of filters on record/list views,
     *       so it should be set to false).
     *
     *     - `show_actions`: this will determine whether or not the
     *       `delete`, `reset`, and `cancel` action buttons will be rendered on
     *       the `filter-actions` view.
     *
     *     @example
     *     <pre><code>
     *         'layout' => array(
     *              'type' =>'filterpanel',
     *              'meta' => array(
     *                  'filter_options' => array(
     *                      'auto_apply' => false,
     *                      'stickiness' => false,
     *                      'show_actions' => false,
     *                  ),
     *              ),
     *          ),
     *     </code></pre>
     */
    initialize: function(opts) {
        // The filter options default to true.
        var defaultOptions = {
            'auto_apply': true,
            'stickiness': true,
            'show_actions': true
        };

        var moduleMeta = app.metadata.getModule(opts.module) || {};
        this.disableActivityStreamToggle(opts.module, moduleMeta, opts.meta || {});

        this.on("filterpanel:change:module", function(module, link) {
            this.currentModule = module;
            this.currentLink = link;
        }, this);

        this.on('filter:create:open', _.debounce(function() {
            // This debounce method should be in accordance with filter-rows::openForm,
            // so components show up at the same time
            this.$('.filter-options').removeClass('hide');
        }, 100, true), this);

        this.on('filter:create:close', function() {
            this.$('.filter-options').addClass('hide');
        }, this);

        // This is required, for example, if we've disabled the subapanels panel so that app doesn't attempt to render later
        this.on('filterpanel:lastviewed:set', function(viewed) {
            this.toggleViewLastStateKey = this.toggleViewLastStateKey || app.user.lastState.key('toggle-view', this);
            var lastViewed = app.user.lastState.get(this.toggleViewLastStateKey);
            if (lastViewed !== viewed) {
                app.user.lastState.set(this.toggleViewLastStateKey, viewed);
            }
        }, this);

        this._super("initialize", [opts]);

        // Set the filter that's currently being edited.
        this.context.editingFilter = null;

        // Obtain any options set in the metadata and override the defaultOptions with them
        // to set on the context.
        var filterOptions = _.extend(defaultOptions, this.meta.filter_options, this.context.get('filterOptions'));
        this.context.set('filterOptions', filterOptions);

        // The `defaultModule` will either evaluate to the model's module (more
        // specific, and used on dashablelist filters), or the module on the
        // current context.
        var lastViewed = app.user.lastState.get(this.toggleViewLastStateKey),
            defaultModule = this.module || this.model.get('module') || this.context.get('module');

        this.trigger('filterpanel:change:module', (moduleMeta.activityStreamEnabled && lastViewed === 'activitystream') ? 'Activities' : defaultModule);
    },

    /**
     * Applies last filter
     * @param {Collection} collection the collection to retrieve the filter definition
     * @param {String} condition(optional) You can specify a condition in order to prevent applying filtering
     */
    applyLastFilter: function(collection, condition) {
        var triggerFilter = true;
        if (_.size(collection.origFilterDef)) {
            if (condition === 'favorite') {
                //Here we are verifying the filter applied contains $favorite otherwise we don't really care about
                //refreshing the listview
                triggerFilter = !_.isUndefined(_.find(collection.origFilterDef, function(value, key) {
                    return key === '$favorite' || (value && !_.isUndefined(value.$favorite));
                }));
            }
            if (triggerFilter) {
                var query = this.$('.search input.search-name').val();
                this.trigger('filter:apply', query, collection.origFilterDef);
            }
        }
    },

    /**
     * Disables the activity stream toggle if activity stream is not enabled for a module
     * @param {String} moduleName The name of the module
     * @param {Object} moduleMeta The metadata for the module
     * @param {Object} viewMeta The metadata for the component
     */
    disableActivityStreamToggle: function(moduleName, moduleMeta, viewMeta) {
        if (moduleName !== 'Activities' && !moduleMeta.activityStreamEnabled) {
            _.each(viewMeta.availableToggles, function(toggle) {
                if (toggle.name === 'activitystream') {
                    toggle.disabled = true;
                    toggle.label = 'LBL_ACTIVITY_STREAM_DISABLED';
                }
            });
        }
    },

    /**
     * @override
     * @private
     */
    _render: function() {
        this._super('_render');
        // `filter-rows` view is outside of `filter` layout and is rendered after `filter` layout is rendered.
        // Now that we are able to preserve last search, we need to initialize filter only once all the filter
        // components rendered.
        this.trigger('filter:reinitialize');
    }
})
