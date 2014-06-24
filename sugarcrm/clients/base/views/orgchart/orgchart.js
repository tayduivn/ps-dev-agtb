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
 * View that displays a list of models pulled from the context's collection.
 *
 * @class View.Views.Base.OrgchartView
 * @alias SUGAR.App.view.views.BaseOrgchartView
 * @extends View.View
 */
({
    events: {
        'click .zoom-control': 'zoomChart',
        'click .toggle-control': 'toggleChart'
    },
    plugins: ['Dashlet', 'Tooltip', 'Chart'],

    // user configurable
    nodetemplate: null,
    reporteesEndpoint: '',
    zoomExtents: null,
    nodeSize: null,

    // private
    jsTree: null,
    slider: null,
    sliderZoomIn: null,
    sliderZoomOut: null,

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize: function(options) {
        var self = this;
        this._super('initialize', [options]);

        // custom renderer for tree node
        this.nodetemplate = app.template.getView(this.name + '.orgchartnode');
        //TODO: change api to accept id as param or attrib as object to produce
        this.reporteesEndpoint = app.api.buildURL('Forecasts', 'orgtree/' + app.user.get('id'), null, {'level': 2});
        this.zoomExtents = {'min': 0.25, 'max': 2};
        this.nodeSize = {'width': 124, 'height': 56};

        this.chart = nv.models.tree()
                .duration(300)
                .nodeSize(this.nodeSize)
                .nodeRenderer(function(d) {
                    return self.nodetemplate(d.metadata);
                })
                .zoomExtents(self.zoomExtents)
                .horizontal(false)
                .getId(function(d) {
                    return d.metadata.id;
                });
    },

    /**
     * Returns a url to a user record
     * @param {String} id the User record id.
     * @protected
     */
    _buildUserUrl: function(id) {
        return '#' + app.bwc.buildRoute('Employees', id);
    },

    /**
     * Generic method to render chart with check for visibility and data.
     * Called by _renderHtml and loadData.
     */
    renderChart: function() {
        var self = this;
        if (!this.isChartReady()) {
            return;
        }

        // chart controls
        this.slider = this.$('.btn-slider .noUiSlider');
        this.sliderZoomIn = this.$('.btn-slider i[data-control="zoom-in"]');
        this.sliderZoomOut = this.$('.btn-slider i[data-control="zoom-out"]');

        //zoom slider
        this.slider.noUiSlider('init', {
            start: 100,
            knobs: 1,
            scale: [25, 200],
            connect: false,
            step: 25,
            change: function() {
                if (!self.chart_loaded) {
                    return;
                }
                var values = self.slider.noUiSlider('value'),
                    scale = self.chart.zoomLevel(values[0] / 100);
                self.sliderZoomIn.toggleClass('disabled', (scale === self.zoomExtents.max));
                self.sliderZoomOut.toggleClass('disabled', (scale === self.zoomExtents.min));
            }
        });

        //jsTree control for selecting root node
        var $jsTree = this.$('div[data-control="org-jstree"]');
        this.jsTree = $jsTree.jstree({
                // generating tree from json data
                'json_data': {
                    'data': this.chartCollection
                },
                // plugins used for this tree
                'plugins': ['json_data', 'ui', 'types'],
                'core': {
                    'animation': 0
                },
                'ui': {
                    // when the tree re-renders, initially select the root node
                    'initially_select': ['jstree_node_' + app.user.get('user_name')]
                }
            })
            .on('loaded.jstree', function(e) {
                // do stuff when tree is loaded
                self.$('div[data-control="org-jstree"]').addClass('jstree-sugar');
                self.$('div[data-control="org-jstree"] > ul').addClass('list');
                self.$('div[data-control="org-jstree"] > ul > li > a').addClass('jstree-clicked');
            })
            .on('click.jstree', function(e) {
                e.stopPropagation();
                e.preventDefault();
            })
            .on('select_node.jstree', function(event, data) {
                var jsData = data.inst.get_json();

                self.chart.filter(jQuery.data(data.rslt.obj[0], 'id'));
                self.forceRepaint();
                self.$('div[data-control="org-jstree-dropdown"] .jstree-label').text(data.inst.get_text());
                data.inst.toggle_node(data.rslt.obj);
            });
        app.accessibility.run($jsTree, 'click');

        d3.select('svg#' + this.cid)
            .datum(this.chartCollection[0])
            .transition().duration(700)
            .call(this.chart);

        this.forceRepaint();

        this.$('.nv-expcoll').on('click', function(e) {
            self.forceRepaint();
        });

        this.chart_loaded = _.isFunction(this.chart.resize);
        this.displayNoData(!this.chart_loaded);
    },

    /**
     * Forces repaint of images using opacity animation to fix
     * issue with rendering foreignObject in SVG
     */
    forceRepaint: function() {
        self.$('.rep-avatar').on('load', function() {
            $(this).removeClass('loaded').addClass('loaded');
        });
    },

    /**
     * Override the hasChartData method in Chart plugin because
     * this view does not have a total value.
     */
    hasChartData: function() {
        return !_.isEmpty(this.chartCollection);
    },

    /**
     * Override the chartResize method in Chart plugin because
     * orgchart nvd3 model uses resize instead of update.
     */
    chartResize: function() {
        this.chart.resize();
    },

    /**
     * Recursively step through the tree and for each node representing a tree node, run the data attribute through
     * the _postProcessTree function.  This function supports n-levels of the tree hierarchy.
     *
     * @param data The data structure returned from the REST API Forecasts/reportees endpoint
     * @return The modified data structure after all the parent and children nodes have been stepped through
     * @private
     */
    _postProcessTree: function(data) {
        var root = [],
            self = this;

        if (_.isArray(data) && data.length == 2) {
            root.push(data[0]);
            root[0].children.push(data[1]);
        } else {
            root.push(data);
        }

        //protect against admin and other valid Employees
        if (_.isEmpty(root[0].metadata.id)) {
            return null;
        }

        _.each(root, function(entry) {
            var adopt = [];

            //Scan for the nodes with the data attribute.  These are the nodes we are interested in
            if (!entry.data) {
                return;
            }

            entry.metadata.url = self._buildUserUrl(entry.metadata.id);
            entry.metadata.img = app.api.buildFileURL({
                module: 'Employees',
                id: entry.metadata.id,
                field: 'picture'
            });

            if (!entry.children) {
                return;
            }

            //For each children found (if any) then call _postProcessTree again.
            _.each(entry.children, function(childEntry) {
                if (entry.metadata.id !== childEntry.metadata.id) {
                    var newChild = self._postProcessTree(childEntry);
                    if (!_.isEmpty(newChild)) {
                        adopt.push(newChild[0]);
                    }
                }
            }, this);

            entry.children = adopt;

        }, this);

        return root;
    },

    /**
     * Slider control for zooming chart viewport.
     * @param {e} event The event object that is triggered.
     */
    zoomChart: function(e) {
        if (!this.chart_loaded) {
            return;
        }

        var button = $(e.target),
            scale = this.chart.zoom(button.data('control') === 'zoom-in' ? 0.25 : -0.25);

        this.sliderZoomIn.toggleClass('disabled', (scale === this.zoomExtents.max));
        this.sliderZoomOut.toggleClass('disabled', (scale === this.zoomExtents.min));

        this.slider.noUiSlider('move', {to: scale * 100});
    },

    /**
     * Handle all chart manipulation toggles.
     * @param {e} event The event object that is triggered.
     */
    toggleChart: function(e) {
        if (!this.chart_loaded) {
            return;
        }

        //if icon clicked get parent button
        var button = $(e.currentTarget).hasClass('btn') ? $(e.currentTarget) : $(e.currentTarget).parent('.btn');

        switch (button.data('control')) {
            case 'orientation':
                this.chart.orientation();
                button.find('i').toggleClass('icon-arrow-right icon-arrow-down');
                break;

            case 'show-all-nodes':
                this.chart.showall();
                this.forceRepaint();
                break;

            case 'zoom-to-fit':
                this.chart.reset();
                this.slider.noUiSlider('move', {to: 100});
                break;

            default:
        }
    },

    /**
     * @inheritDoc
     */
    loadData: function(options) {
        var self = this;

        app.api.call('get', this.reporteesEndpoint, null, {
            success: function(data) {
                self.chartCollection = self._postProcessTree(data);
                if (!self.disposed) {
                    self.renderChart();
                }
            },
            complete: options ? options.complete : null
        });
    },

    /**
     * overriding _dispose to make sure custom added event listeners are removed
     * @private
     */
    _dispose: function() {
        if (this.jsTree) {
            this.jsTree.off();
        }
        if (this.slider) {
            this.slider.off('move');
        }
        this._super('_dispose');
    }
})
