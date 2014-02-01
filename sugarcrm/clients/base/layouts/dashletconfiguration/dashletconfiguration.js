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
({
    /**
     * TRUE If we are configuring a dashlet of type dashable list, FALSE otherwise.
     *
     * @property {Boolean}
     */
    isDashableList: false,

    initialize: function(options) {
        var meta = app.metadata.getLayout(options.module, options.name),
            main_panel;

        _.each(meta.components, function(component) {
            main_panel = _.find(component.layout.components, function(childComponent) {
                return childComponent.layout && childComponent.layout.name === 'main-pane';
            }, this);
        }, this);

        if (main_panel) {
            main_panel.layout.components = _.union(main_panel.layout.components, options.meta.components);
            this.isDashableList = !!_.find(options.meta.components, function(comp) {
                if(comp.view) {
                    return comp.view.type === 'dashablelist';
                }
            });

            // Append the filter layout component to the metadata.
            if (this.isDashableList) {
                var filterPanelLayoutDef = app.metadata.getView(null, 'dashablelist');
                if (filterPanelLayoutDef && filterPanelLayoutDef.filter_panel) {
                    main_panel.layout.components.push(filterPanelLayoutDef.filter_panel);
                }
            }
        }

        options.meta = meta;
        app.view.Layout.prototype.initialize.call(this, options);

        if (this.isDashableList) {
            var filterOptions = {
                'applyFilter': false,
                'saveLastFilter': false,
                'hideFilterActions': true
            };
            this.context.set(filterOptions);
        }

        this.listenTo(this.context, 'dashletconfig:save', this.saveDashlet);
        this.listenTo(this.context, 'filter:add', this.updateDashletFilterAndSave);
    },

    /**
     * This function is invoked by the `dashletconfig:save` event. If the dashlet
     * we are saving is a dashable list, it initiates the save process for a new
     * filter on the appropriate module's list view, otherwise, it takes the
     * `currentFilterId` stored on the context, and saves it on the dashlet.
     *
     * @param {Bean} model The dashlet model.
     */
    saveDashlet: _.debounce(function() {
        if (this.isDashableList) {
            if (this.context.editingFilter) {
                // We are editing/creating a new filter
                if (!this.context.editingFilter.get('name')) {
                    this.context.editingFilter.set('name', app.lang.get('LBL_DASHLET') +
                        ': ' + this.model.get('label'));
                }
                this.context.trigger('filter:create:save');
            } else {
                // We are saving a dashlet with a predefined filter
                var filterId = this.context.get('currentFilterId'),
                    obj = {id: filterId};
                this.updateDashletFilterAndSave(obj);
            }
        } else {
            app.drawer.close(this.model);
        }
    }, 200),

    /**
     * This function is invoked by the `filter:add` event. It saves the
     * filter ID on the dashlet model prior to saving it, for later reference.
     *
     * @param {Bean} filterModel The saved filter model.
     */
    updateDashletFilterAndSave: function(filterModel) {
        // We need to save the filter ID on the dashlet model before saving
        // the dashlet.
        var id = filterModel.id || filterModel.get('id');
        this.model.set('filterId', id);
        app.drawer.close(this.model);

        // We need to refresh the controller context in this case, because
        // of the limitations in the filter architecture. The main reason
        // why we do this is because the filter collection is not shared
        // amongst views and therefore changes to this collection on different
        // contexts (list views and dashlets) need to be kept in sync.
        //
        // TODO: This will break the dashboard edit page.
        app.controller.context.reloadData({'recursive': false});
        app.controller.layout.render();
    }
})
