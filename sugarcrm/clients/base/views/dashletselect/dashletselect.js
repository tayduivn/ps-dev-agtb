/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    events: {
        "click .select" : "selectClicked",
        'click .preview' : "previewClicked"
    },
    previewClicked: function(evt) {
        var index = $(evt.currentTarget).data('index');
        var collection = this.context.get("dashlet_collection");
        this.previewDashlet(collection[index].metadata);
    },
    previewDashlet: function(metadata) {
        var layout = this.layout;
        while(layout) {
            if(_.isFunction(layout.showPreviewPanel)) {
                layout.showPreviewPanel();
                break;
            }
            layout = layout.layout;
        }

        var previewLayout = layout.getComponent('preview-pane');
        if(previewLayout) {
            var previousComponent = _.last(previewLayout._components);
            if(previousComponent.name !== "dashlet-preview") {
                var index = previewLayout._components.length - 1;
                previewLayout._components[index].dispose();
                previewLayout.removeComponent(index);
            }
            previewLayout._addComponentsFromDef([
                {
                    layout: {
                        type: 'dashlet',
                        label: metadata.name,
                        preview : true,
                        components: [{
                            view: metadata.type,
                            context: _.extend({
                                forceNew: true,
                                dashlet: _.extend({
                                    name: metadata.name,
                                    type: metadata.type,
                                    viewName: 'preview'
                                },metadata.preview)
                            }, (metadata.config.module || metadata.config.model) ? {
                                    module: metadata.config.module,
                                    model: metadata.config.model
                                } : {
                                    model: this.model
                                }
                            )
                        }]
                    }
                }
            ]);
            previewLayout.loadData();
            previewLayout.render();
        }
    },
    selectClicked: function(evt) {
        var index = $(evt.currentTarget).data('index');
        var collection = this.context.get("dashlet_collection");
        this.selectDashlet(collection[index].metadata);
    },
    selectDashlet: function(metadata) {
        app.drawer.load({
            layout: {
                name: 'dashletconfiguration',
                components: [
                    {
                        view: metadata.type,
                        context: {
                            model: new app.Bean(),
                            dashlet: _.extend({
                                name: metadata.name,
                                type: metadata.type,
                                viewName: 'config'
                            },{
                                module: metadata.module
                            }, metadata.config)
                        }
                    }
                ]
            },
            context: {
                module: this.module,
                model: new app.Bean(),
                forceNew: true
            }
        });
    },
    _render: function() {
        app.view.View.prototype._render.call(this);
        var self = this;
        if(this.context.get("dashlet_collection")) {
            this.$("#dashletList").dataTable({
                "bFilter": false,
                "bInfo":false,
                "bPaginate": false,
                "aaData": _.pluck(this.context.get("dashlet_collection"), 'table'),
                "aoColumns": [
                    {
                        sTitle: "Name"
                    },
                    {
                        sTitle: "Description"
                    },
                    {
                        sTitle: "Actions",
                        fnRender: function(obj) {
                            return '<a class="select" data-index="' + obj.iDataRow + '" href="javascript:void(0);">Select and edit</a>';
                        }
                    },
                    {
                        sTitle: "",
                        fnRender: function(obj) {
                            return '<a class="preview" data-index="' + obj.iDataRow + '" href="javascript:void(0);"><i class=icon-eye-open></i></a>';
                        }
                    }
                ]
            });
        }

    },
    loadData: function() {
        var dashlet_collection = this.context.get("dashlet_collection");
        if(!dashlet_collection) {
            dashlet_collection = [];
            var sortedModuleList = _.sortBy(JSON.parse(JSON.stringify(app.metadata.getModuleNames())), function(name) {
                return name;
            });

            _.each(app.view.views, function(view, name){
                if(view.prototype.plugins && view.prototype.plugins.indexOf('Dashlet') >= 0) {

                    var component = this.parseComponentName(name, sortedModuleList);
                    var parentDashlet = _.find(dashlet_collection, function(dashlet) {
                        return dashlet.type === component.name;
                    }, this);
                    var metadata = app.metadata.getView(component.module, component.name);
                    if(!parentDashlet && metadata.dashlets) {
                        _.each(metadata.dashlets, function(dashlet) {
                            dashlet_collection.push({
                                type: component.name,
                                metadata: _.extend({
                                    component: name,
                                    module: component.module,
                                    type: component.name
                                }, dashlet),
                                table: [
                                    dashlet.name,
                                    dashlet.description,
                                    '',
                                    ''
                                ]
                            });
                        }, this);
                    }
                }
            }, this);
            this.context.set("dashlet_collection", dashlet_collection);
            this.render();
        }
    },
    parseComponentName: function(name, modules){
        name = name.replace(/\W+/g, '-').replace(/([a-z\d])([A-Z])/g, '$1-$2');
        var chunk = name.split('-'),
            module,
            type;
        if(_.indexOf(modules, chunk[0], true) >= 0) {
            module = chunk[0];
            chunk.splice(0, 1);
        }
        return {
            module: module,
            type: chunk.pop(),
            name: chunk.join('-').toLowerCase()
        };
    }
})
