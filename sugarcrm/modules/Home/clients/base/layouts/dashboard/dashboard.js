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
    // TODO we should be able to call/use other template files
    emptyHbt: Handlebars.compile('<div class="widget empty" data-action="addDashlet"><p>Add Dashlet</p><div class="add-dashlet"><strong>+</strong></div></div>'),
    droppableHbt: Handlebars.compile('<li class="span{{width}} widget-container" data-dashletid="{{dashletId}}">{{#each row}}<div class="widget empty" data-action="addDashlet"><p>Add Dashlet</p><div class="add-dashlet"><strong>+</strong></div></div>{{/each}}</li>'),
    rowHbt: Handlebars.compile('<li class="row-fluid"><ul>{{#each row}}<li class="span{{width}} widget-container" data-dashletid="{{dashletId}}"><div class="widget empty" data-action="addDashlet"><p>Add Dashlet</p><div class="add-dashlet"><strong>+</strong></div></div></li>{{/each}}</ul></li>'),
    isInit: false,
    events: {
        'click [data-action="addRow"]' : 'addRow',
        'click [data-action="addDashlet"]': '_createDashlet',
        'click .switchBoard': 'switchBoardCallback',
        'click .createBoard': 'createBoardCallback',
        'click .saveBoard': 'saveBoard',
        'click [data-action="delete"]':'clickDeleteCallback',
        'click .deleteBoard':'deleteBoard',
       'click .editName':'toggleNameEdit'
    },

    initialize:function (options) {
        // deep clone the default meta for the create page
        if (!this.defaultMeta) {
            this.defaultMeta = JSON.parse(JSON.stringify(options.meta));
        }
        this.options = options;
        var self = this;
        this.dashboards = app.user.get('dashboards');

        var lastDashboardId = app.cache.get("currentDashboardId");
        if (lastDashboardId && this.dashboards && !self.isInit) {
            _.each(this.dashboards, function (dashboard) {
                if (dashboard.id == lastDashboardId) {
                    var url = app.api.buildURL("Dashboards", "read", {id:lastDashboardId});
                    app.api.call("read", url, {}, {success:function (data) {
                        if (data.metadata) {
                            self.boardName = data.name;
                            self.currentBoardId = data.id;
                            self.options.meta = data.metadata;
                            self.isInit = true;
                            self.initialize(self.options);
                            self.render();
                        }
                    }});
                }
            });
            options.meta.components = [];
        }
        app.view.Layout.prototype.initialize.call(this, options);
        this.context.off("dashboard:dashlet:configure", null, this);
        this.context.on("dashboard:dashlet:configure", this._configureDashlet, this);

        this.context.off("dashboard:dashlet:update", null, this);
        this.context.on("dashboard:dashlet:update", this.updateAndSave, this);
        this.$('.boardName').hide();
        this.isInit = true;
        this.loadData();
    },
    toggleNameEdit:function (event) {
        this.$('.nameContainer').toggle();
        this.$('.boardName').toggle();
    },
    clickDeleteCallback: function(event) {
        var $container = this.$(event.target).closest('.widget-container');
        var dashletID = $container.data('dashletid');
        this.deleteDashlet(dashletID);
        $container.empty().append(this.emptyHbt());
    },
    deleteBoard: function(event){
       var id = this.$(event.target).data('id')
        var url = app.api.buildURL("Dashboards","delete",{id:id});
        var self = this;
        app.api.call("delete",url,{},{success:function(data){
            self.updateUser({id:id}, true);
            self.$(event.target).parent().remove();
            app.alert.show("scd",{messages:"Board deleted.", autoClose: true});
        }});
    },

    deleteDashlet: function(dashletID) {
        var self = this;
        _.each(this.meta.components, function(row,rowKey){
            _.each(row, function(dashlet,dashletKey) {
                if (dashlet.dashletId == dashletID) {
                    self.meta.components[rowKey][dashletKey] = {
                        dashletId: dashletID,
                        width : dashlet.width
                    }
                }
            });
        });
    },

    // this data is denormalized so we need to update user too
    updateUser: function(layoutData, remove) {
        var newBoard = {
            id:layoutData.id,
            name: layoutData.name,
            url: app.api.buildURL("Dashboards","read",layoutData)
            }, isNew = true;

        if (app.user.attributes.dashboards) {
            _.each(app.user.attributes.dashboards, function(dashboard){
                if (dashboard.id == newBoard.id) {
                    dashboard.name = newBoard.name;
                    isNew = false;
                }
            });
            if (isNew) {
                app.user.attributes.dashboards.push(newBoard);
            }
        } else {
            app.user.attributes.dashboards = [newBoard];
        }
        if (remove) {
            var id = layoutData.id;
            var dashboards = app.user.attributes.dashboards;
            _.each(dashboards, function(dashboard, index) {
                if (id == index) {
                    dashboards.splice(index,1);
                }
            })
        }
    },

    /**
     * Gives components unique IDs so we can find them
     */
    addressComponents: function() {
        _.each(this.meta.components, function(col) {
            _.each(col.rows, function(row) {
                _.each(row, function(component) {
                    component.dashletId = _.uniqueId();
                });
            });
        });
    },

    /**
     * Switches boards
     * @param event
     */
    switchBoardCallback: function(event) {
        var self = this;
        var boardID = this.$(event.target).data('id');
        this.switchBoard(boardID);
    },
    switchBoard: function(boardID) {
        var self = this;
        _.each(this.dashboards, function(board) {
            if(boardID == board.id) {
                app.api.call("read",board.url,{},{success: function(data) {
                    if (data.metadata) {
                        self.boardName = data.name;
                        self.currentBoardId = data.id;
                        self.options.meta = data.metadata;
                        self.initialize(self.options);
                        self.render();
                        app.cache.set("currentDashboardId",data.id);
                    }
                }});
            }
        });
    },

    /**
     * saves the current board, creates a new one if not on a board that's saved
     * @param event
     */
    saveBoard:function (event) {
        var name = this.$('.boardName').val();
        if (this.currentBoardId) {
            this.updateDashboard(this.currentBoardId, this.meta, name);
            var layoutData = {
                name: name,
                id: this.currentBoardId
            }
            this.updateUser(layoutData);
        } else  {
            this.createDashboard(this.meta, name);
        }
        this.boardName = name;
        this.options.meta = this.meta;
        this.initialize(this.options);
        this.render();
    },

    _addComponentsFromDef: function(components) {

        this.addressComponents();

        // TODO WORKAROUND we need to manually do a render because there is no _renderHTML in layouts
        if (this.template) {
            this.$el.html(this.template(this));
        }
        this.delegateEvents();
        // deep clone
        var clonedComponents = JSON.parse(JSON.stringify(components));
        var flatComponents = [];
        // massage metadata so we can place them without trying
        _.each(clonedComponents, function(col){
            _.each(col.rows, function(row) {
                _.each(row, function(component) {
                    flatComponents.push(component);
                });
            });
        });
        app.view.Layout.prototype._addComponentsFromDef.call(this, flatComponents);
    },
    createBoardCallback: function(event) {
      this.createDashboard(this.defaultMeta);
    },
    createDashboard: function(metadata, name) {
        var self = this;
        var url = app.api.buildURL("Dashboards","create");
        var payload = {
            name:name || "My New Dashboard",
            metadata: metadata
        };
        app.api.call("create",url,payload,{success:function(data){
            app.alert.show("scb",{messages:"Board created!", autoClose: true});
            self.updateUser(data);
            self.boardName = data.name;
            self.currentBoardId = data.id;
            self.options.meta = data.metadata;
            self.initialize(self.options);
            self.render();
        }});
    },

    updateDashboard: function(id, metadata, name){
        var self = this;
        var url = app.api.buildURL("Dashboards","update",{id:id});
        var payload = {
            name: name || "Dashboard",
            metadata: metadata
        };
        app.api.call("update",url,payload,{success:function(data){
            app.alert.show("scb",{messages:"Board saved.", autoClose: true});
            self.boardName = data.name;
            self.currentBoardId = data.id;
            self.options.meta = data.metadata;
        }});
    },
    updateAndSave: function (metadata) {
      this.updateDashlet(metadata);
        this.saveBoard({});
        this.initialize(this.options);
        this.render();
    },
    updateDashlet: function (metadata) {
        var found = false;

        for (var row = 0; row < this.meta.components.length && !found; row++) {
            for (var col = 0; col < this.meta.components[row].length && !found; col++) {
                if (this.meta.components[row][col].dashletId == metadata.dashletId) {
                    // hack to make sure the width is retained as a part of the metadata
                    metadata.width = this.meta.components[row][col].width;

                    // overwrite the metadata
                    this.meta.components[row][col] = metadata;
                    found = true;
                }
            }
        }
    },
    render: function() {
        app.view.Layout.prototype.render.call(this);
        if (!this.toggled) {
            app.controller.context.trigger('toggleSidebar');
            this.toggled = true;
        }

    },
    getDashletMeta: function(id) {
        var resultDashlet = null;
        _.each(this.meta.components, function(col) {
            _.each(col.rows, function(row) {
                _.each(row, function(dashlet) {
                   if (id == dashlet.dashletId) {
                       resultDashlet = dashlet;
                   }
                });
            });
        });
        return resultDashlet;
    },
    getDashletView: function(id) {
        var resultDashlet = null;
            _.each(this._components, function(dashletView) {
                if (id == dashletView.parentDef.dashletId) {
                    resultDashlet = dashletView;
                }
            });
        return resultDashlet;
    },


    _render: function() {
        app.view.Layout.prototype._render.call(this);
        this.applyDragAndDrop();
    },

    addRow: function(event) {
        event.stopPropagation();
        var newRow = [];
        var columns = parseInt(this.$(event.currentTarget).data('columns'), 10);
        for (var i =0; i < columns; i++) {
            newRow[i] = {
                width: 12/columns,
                dashletId : _.uniqueId()
            };
        }
        // Add to meta
        this.meta.components.push(newRow);
        // add to dom
        this.$('.rows').append(this.rowHbt({row: newRow}));

        this.applyDragAndDrop();
    },

    applyDragAndDrop: function() {

        var self = this;
        this.$('.widget:not(.empty)').draggable({
            revert: 'invalid',
            stack: '.widget:not(.helper)',
            handle: 'h4',
            cursorAt: {
                left: 150,
                top: 16
            },
            start: function(event, ui) {
                $(this).hide();
                self.$el.find('.widget:not(.helper) .widget-content').hide();
            },
            stop: function() {
                $(this).show();
                self.$el.find('.widget-content').show();
            },
            helper: function() {
                var $clone = $(this).clone();
                $clone.addClass('span4 helper');
                $clone.find('.btn-toolbar').remove();
                $clone.find('.widget-content').empty();
                return $clone;
            }
        });

        this.$('.widget-container').droppable({
            activeClass: 'ui-droppable-active',
            hoverClass: 'ui-droppable-hover',
            tolerance: 'pointer',
            accept: function() {
                return self.$(this).find('.widget.empty').length === 1;
            },
            drop: function(event, ui) {
                debugger;
                var $this = self.$(this);
                var sourceID = ui.draggable.parent().data('dashletid');
                var destinationID = $this.data('dashletid');

                // update our metadata
                var sourceDashletMeta = _.clone(self.getDashletMeta(sourceID));
                if(sourceDashletMeta) {
                    sourceDashletMeta.dashletId = destinationID;
                    self.updateDashlet(sourceDashletMeta);
                    self.deleteDashlet(sourceID);
                    self.updateDashboard(self.currentBoardId, self.meta, self.boardName);

                    // update our views
                    var sourceDashletView = self.getDashletView(sourceID);
                    sourceDashletView.parentDef.dashletId = destinationID;
                    self._placeComponent(sourceDashletView, sourceDashletView.parentDef);
                    $this.empty();
                    ui.draggable.closest('.widget-container').append(self.emptyHbt());
                    ui.draggable.appendTo($this).css({left: 0, top: 0});
                }

            }
        });
    },

    _save: function() {
        console.log("%cSave!!!", "color:orange");
    },

    /**
     * Places a view's element on the page. This should be overriden by any custom layout types.
     * In layout defs, the child component should have a `span` definition corresponding to the bootstrap scaffold.
     * @param {View.View} comp
     * @protected
     * @method
     */
    _placeComponent: function(comp, def) {
        debugger;
        comp.parentDef = def;
        var target = this.$('[data-dashletid='+def.dashletId+'] .widget-content')[0];
        this.$(target).empty().append(comp.el);
    },

    _createDashlet: function(evt) {
        var dashletId = this.$(evt.currentTarget.parentElement).data("dashletid");

        // open the drawer layout
        this.layout.trigger("dashlet:create:fire", {
            components: [{
                layout:  "dashletselect", // add the dashletselect layout
                context: { dashletId: dashletId }
            }]
        }, this);
    },

    _configureDashlet: function(context) {
        context = context || {};

        // open the drawer layout
        this.layout.trigger("dashlet:create:fire", {
            components: [{
                layout:  "dashletconfiguration", // add the dashletconfiguration layout
                context: context
            }]
        }, this);
    }
})
