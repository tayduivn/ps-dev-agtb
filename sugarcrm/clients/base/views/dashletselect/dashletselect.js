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
    events: {
        'click .select' : 'selectClicked',
        'click .preview' : 'previewClicked',
        'keyup .search' : 'searchFired'
    },
    dataTable: null,

    /**
     * Triggers dataTable filter to search the typed string
     *
     * @param {Event} evt Window event.
     */

    searchFired: function(evt) {
        var value = $(evt.currentTarget).val();
        this.dataTable.fnFilter(value, 0);
    },

    /**
     * Operation when user clicks preview
     *
     * @param {Event} evt Window event.
     */
    previewClicked: function(evt) {
        var index = $(evt.currentTarget).data('index');
        var collection = this.context.get('dashlet_collection');
        this.previewDashlet(collection[index].metadata);
    },

    /**
     * Load dashlet preview by passing preview metadata
     *
     * @param {Object} metadata Preview metadata.
     */
    previewDashlet: function(metadata) {
        var layout = this.layout,
            previewLayout;
        while (layout) {
            if (layout.getComponent('preview-pane')) {
                previewLayout = layout.getComponent('preview-pane').getComponent('dashlet-preview');
                previewLayout.showPreviewPanel();
                break;
            }
            layout = layout.layout;
        }

        if (previewLayout) {
            // If there is no preview property, use the config property
            if (!metadata.preview) {
                metadata.preview = metadata.config;
            }
            var previousComponent = _.last(previewLayout._components);
            if (previousComponent.name !== 'dashlet-preview') {
                var index = previewLayout._components.length - 1;
                previewLayout._components[index].dispose();
                previewLayout.removeComponent(index);
            }

            var contextDef,
                component = {
                    label: app.lang.get(metadata.name, metadata.preview.module),
                    name: metadata.type,
                    preview: true
                };
            if (metadata.preview.module || metadata.preview.link) {
                contextDef = {
                    skipFetch: false,
                    forceNew: true,
                    module: metadata.preview.module,
                    link: metadata.preview.link
                };
            } else if (metadata.module) {
                contextDef = {
                    module: metadata.module
                };
            }

            component.view = _.extend({}, metadata.preview, component);
            if (contextDef) {
                component.context = contextDef;
            }

            previewLayout._addComponentsFromDef([
                {
                    layout: {
                        type: 'dashlet',
                        label: app.lang.get(metadata.name, metadata.preview.module),
                        preview: true,
                        components: [
                            component
                        ]
                    }
                }
            ]);
            previewLayout.loadData();
            previewLayout.render();
        }
    },

    /**
     * Operation when user clicks [Select and Edit]
     *
     * @param {Event} evt Window event.
     */
    selectClicked: function(evt) {
        var index = $(evt.currentTarget).data('index');
        var collection = this.context.get('dashlet_collection');
        this.selectDashlet(collection[index].metadata);
    },

    /**
     * Load dashlet configuration view by passing configuration metadata
     *
     * @param {Object} metadata Configuration metadata.
     */
    selectDashlet: function(metadata) {
        app.drawer.load({
            layout: {
                name: 'dashletconfiguration',
                components: [
                    {
                        view: _.extend({}, metadata.config, {
                            label: app.lang.get(metadata.name, metadata.config.module),
                            name: metadata.type,
                            config: true,
                            module: metadata.config.module || metadata.module
                        })
                    }
                ]
            },
            context: {
                module: metadata.config.module || metadata.module,
                forceNew: true,
                skipFetch: true
            }
        });
    },

    /**
     * {@inheritDoc}
     *
     * After rendering the template, it activates dataTable plugin.
     */
    _render: function() {
        app.view.View.prototype._render.call(this);
        var self = this;
        if (this.context.get('dashlet_collection')) {
            this.dataTable = this.$('#dashletList').dataTable({
                'bFilter': true,
                'bInfo': false,
                'bPaginate': false,
                'aaData': this.getFilteredList(),
                'aoColumns': [
                    {
                        sTitle: app.lang.get('LBL_NAME')
                    },
                    {
                        sTitle: app.lang.get('LBL_DESCRIPTION')
                    },
                    {
                        sTitle: app.lang.get('LBL_LISTVIEW_ACTIONS'),
                        fnRender: function(obj) {
                            return '<a class="select" href="javascript:void(0);" ' +
                                'data-index="' + obj.aData[obj.iDataColumn] + '" ' +
                                '>' + app.lang.get('LBL_LISTVIEW_SELECT_AND_EDIT') + '</a>';
                        },
                        bSortable: false
                    },
                    {
                        sTitle: app.lang.get('LBL_PREVIEW'),
                        fnRender: function(obj) {
                            return '<a class="preview" href="javascript:void(0);" ' +
                                'data-index="' + obj.aData[obj.iDataColumn] + '" ' +
                                '><i class=icon-eye-open></i></a>';
                        },
                        bSortable: false
                    }
                ]
            });
            //hide default search box
            this.$('#dashletList_filter').hide();
        }

    },

    /**
     * Filtering the available dashlets with the current page's module and
     * layout view.
     *
     * @return {Array} A list of filtered dashlet set.
     */
    getFilteredList: function() {
        var parentModule = app.controller.context.get('module'),
            parentView = app.controller.context.get('layout');

        return _.chain(this.context.get('dashlet_collection'))
            .filter(function(dashlet) {
                var filter = dashlet.filter;
                if (_.isUndefined(filter)) {
                    //if filter is undefined, then the dashlet will be in the list
                    return true;
                }
                var filterModules = filter.module || [parentView],
                    filterViews = filter.view || [parentView];
                if (_.isString(filterModules)) {
                    filterModules = [filterModules];
                }
                if (_.isString(filterViews)) {
                    filterViews = [filterViews];
                }
                //if the filter is matched, then it returns true
                return _.contains(filterModules, parentModule) && _.contains(filterViews, parentView);
            })
            .pluck('table')
            .value();
    },

    /**
     * Convert the component metadata to match with dataTable format.
     *
     * @param {Array} components The list of components.
     * @return {Array} The parsed collection format.
     */
    _getDashletCollection: function(components) {
        _.each(components, function(component, index) {
            //FIXME: dataTable should be replace into flex-list
            component['table'] = [
                component.title,
                component.description,
                index,
                index
            ];
        }, this);
        return components;
    },

    /**
     * Iterates dashlets metadata and extract the dashlet components among them.
     *
     * @param {String} type The component type (layout|view).
     * @param {String} name The component name.
     * @param {String} module The module name.
     * @param {Object} meta The metadata.
     * @return {Array} list of available dashlets.
     * @private
     */
    _getDashlets: function(type, name, module, meta) {
        var dashlets = [],
            hadDashlet = meta && meta.dashlets &&
                app.view.componentHasPlugin({
                    type: type,
                    name: name,
                    module: module,
                    plugin: 'Dashlet'
                });
        if (!hadDashlet) {
            return dashlets;
        }
        _.each(meta.dashlets, function(dashlet) {
            if (!dashlet.config) {
                return;
            }
            if (!app.acl.hasAccess('access', module || dashlet.config.module)) {
                return;
            }
            dashlets.push({
                type: name,
                filter: dashlet.filter,
                metadata: _.extend({
                    component: name,
                    module: module,
                    type: name
                }, dashlet),
                title: app.lang.get(dashlet.name, dashlet.config.module),
                description: app.lang.get(dashlet.description, dashlet.config.module)
            });
        }, this);
        return dashlets;
    },

    /**
     * Retrieves all base view's metadata.
     *
     * @return {Array} All base view's metadata.
     * @private
     */
    _addBaseViews: function() {
        var components = [];
        _.each(app.metadata.getView(), function(view, name) {
            var dashlets = this._getDashlets('view', name, null, view.meta);
            if (!_.isEmpty(dashlets)) {
                components = _.union(components, dashlets);
            }
        }, this);
        return components;
    },

    /**
     * Retrieves all module view's metadata.
     *
     * @return {Array} The module view's metadata.
     * @private
     */
    _addModuleViews: function() {
        var components = [];
        _.each(app.metadata.getModuleNames(), function(module) {
            _.each(app.metadata.getView(module), function(view, name) {
                var dashlets = this._getDashlets('view', name, module, view.meta);
                if (!_.isEmpty(dashlets)) {
                    components = _.union(components, dashlets);
                }
            }, this);
        }, this);
        return components;
    },

    /**
     * {@inheritDoc}
     *
     * Instead of fetching context, it will retrieve all dashable components
     * based on metadata.
     */
    loadData: function() {
        var dashletCollection = this.context.get('dashlet_collection');
        if (dashletCollection) {
            return;
        }

        dashletCollection = _.union(this._addBaseViews(), this._addModuleViews());
        this.context.set('dashlet_collection', this._getDashletCollection(dashletCollection));
        this.render();
    }
})
