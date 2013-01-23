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

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    events:{
        'click [class*="orderBy"]':'setOrderBy',
        'mouseenter .rowaction': 'showTooltip',
        'mouseleave .rowaction': 'hideTooltip',
        'mouseenter tr':'showActions',
        'mouseleave tr':'hideActions'
    },
    initialize: function(options) {
        options.meta = $.extend(true, {}, app.metadata.getView(options.module, 'baselist') || {}, options.meta);
        options.meta.type = options.meta.type || 'list';
        if(!_.isUndefined(options.meta.selection) && !_.isUndefined(options.meta.selection.type)) {
            switch (options.meta.selection.type) {
                case "single":
                    options.meta = this.addSingleSelectionAction(options.meta, options.module);
                    break;
                case "multi":
                    options.meta = this.addMultiSelectionAction(options.meta);
                    break;
                default:
                    break;
            }
        }
        if(!_.isUndefined(options.meta.rowactions)) {
            options.meta = this.addRowActions(options.meta);
        }
        app.view.View.prototype.initialize.call(this, options);
        this.template = this.template || app.template.getView('baselist') || app.template.getView('baselist', this.module) || null;
        this.fallbackFieldTemplate = 'list-header';

        this.context.on("list:preview:fire", null, this);
        this.context.on("list:preview:fire", this.previewRecord, this);
    },
    _render:function () {
        var self = this;
        app.view.View.prototype._render.call(this);
        // off prevents multiple bindings for each render
        this.layout.off("list:search:fire", null, this);
        this.layout.off("list:paginate:success", null, this);
        this.layout.on("list:search:fire", this.fireSearch, this);
        this.layout.on("list:paginate:success", this.render, this);
        this.layout.off("list:filter:toggled", null, this);
        this.layout.on("list:filter:toggled", this.filterToggled, this);
        this.layout.off("list:alert:show", null, this);
        this.layout.on("list:alert:show", this.showAlert, this);
        this.layout.off("list:alert:hide", null, this);
        this.layout.on("list:alert:hide", this.hideAlert, this);
        this.layout.off("list:sort:fire", null, this);
        this.layout.on("list:sort:fire", function(collection) {
            if( _.isUndefined(self.context._callbacks) ) {
                // Sorting on a related module, need the parent context instead
                self.context.parent.trigger("preview:collection:change", collection);
            }
            else {
                self.context.trigger("preview:collection:change", collection);
            }
        }, this);

        // Dashboard layout injects shared context with limit: 5. 
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;
    },
    showAlert: function(message) {
        this.$(".alert .container").html(message);
        this.$(".alert").removeClass("hide");
    },
    hideAlert: function() {
        this.$(".alert").addClass("hide");
    },
    filterToggled:function (isOpened) {
        this.filterOpened = isOpened;
    },
    fireSearch:function (term) {
        var options = {
            limit:this.limit || null,
            params:{},
            fields:this.collection.fields || {}
        };
        if(term) {
            options.params.q = term;
        }
        //TODO: This should be handled automagically by the collection by checking its own tie to the context
        if (this.context.get('link')) {
            options.relate = true;
        }
        this.collection.fetch(options);
    },

    /**
     * Sets order by on collection and view
     * @param {Object} event jquery event object
     */
    setOrderBy:function (event) {
        var orderMap, collection, fieldName, nOrder, options, eventTarget, orderBy;
        var self = this;
        //set on this obj and not the prototype
        self.orderBy = self.orderBy || {};

        //mapping for css
        orderMap = {
            "desc":"_desc",
            "asc":"_asc"
        };

        //TODO probably need to check if we can sort this field from metadata
        collection = self.collection;
        eventTarget = self.$(event.target);
        fieldName = eventTarget.data('fieldname');

        // first check if alternate orderby is set for column
        orderBy = eventTarget.data('orderby');
        // if no alternate orderby, use the field name
        if (!orderBy) {
            orderBy = eventTarget.data('fieldname');
        }

        if (!collection.orderBy) {
            collection.orderBy = {
                field:"",
                direction:"",
                columnName:""
            };
        }

        nOrder = "desc";

        // if same field just flip
        if (orderBy === collection.orderBy.field) {
            if (collection.orderBy.direction === "desc") {
                nOrder = "asc";
            }
            collection.orderBy.direction = nOrder;
        } else {
            collection.orderBy.field = orderBy;
            collection.orderBy.direction = "desc";
        }
        collection.orderBy.columnName = fieldName;

        // set it on the view
        self.orderBy.field = orderBy;
        self.orderBy.direction = orderMap[collection.orderBy.direction];
        self.orderBy.columnName = fieldName;

        // Treat as a "sorted search" if the filter is toggled open
        options = self.filterOpened ? self.getSearchOptions() : {};

        // If injected context with a limit (dashboard) then fetch only that 
        // amount. Also, add true will make it append to already loaded records.
        options.limit = self.limit || null;
        options.success = function () {
            self.layout.trigger("list:sort:fire", collection, self);
            self.render();
        };
        if (this.context.get('link')) {
            options.relate = true;
        }

        // refetch the collection
        collection.fetch(options);
    },
    getSearchOptions:function () {
        var collection, options, previousTerms, term = '';
        collection = this.context.get('collection');

        // If we've made a previous search for this module grab from cache
        if (app.cache.has('previousTerms')) {
            previousTerms = app.cache.get('previousTerms');
            if (previousTerms) {
                term = previousTerms[this.module];
            }
        }
        // build search-specific options and return
        options = {
            params:{},
            fields:collection.fields ? collection.fields : this.collection
        };
        if(term) {
            options.params.q = term;
        }
        if (this.context.get('link')) {
            options.relate = true;
        }
        return options;
    },
    /**
     * Display a Preview for a record in the list view
     * @param model Model for the record to be displayed in Preview
     */
    previewRecord: function(model) {
        if( _.isUndefined(this.context._callbacks) ) {
            // Clicking preview on a related module, need the parent context instead
            this.context.parent.trigger("renderPreview", model, this.collection);
        }
        else {
            this.context.trigger("renderPreview", model, this.collection);
        }
    },
    addSingleSelectionAction: function(meta, module) {
        meta = $.extend(true, {}, meta);
        _.each(meta.panels, function(panel){
            var singleSelect = [{
                'type' : 'selection',
                'name' : meta.selection.name || module + '_select',
                'sortable' : false,
                'label' : meta.selection.label || ''
            }];

            panel.fields = singleSelect.concat(panel.fields);
        });

        return meta;
    },
    addMultiSelectionAction: function(meta) {
        meta = $.extend(true, {}, meta);
        _.each(meta.panels, function(panel){
            var multiSelect = [{
                'type' : 'fieldset',
                'fields' : [{
                    'type' : 'actionmenu',
                    'buttons' : []
                }],
                'value' : false,
                'sortable' : false
            }];
            if (!_.isUndefined(meta.selection.actions)) {
                multiSelect[0].fields[0].buttons = meta.selection.actions;
            }
            panel.fields = multiSelect.concat(panel.fields);
        });

        return meta;
    },
    addRowActions: function(meta) {
        meta = $.extend(true, {}, meta);
        _.each(meta.panels, function(panel){
            var rowActions = {
                'type' : 'fieldset',
                'fields' : [{
                    'type' : 'rowactions',
                    'buttons' : []
                }],
                'value' : false,
                'sortable' : false,
                'label' : meta.rowactions.label || ''
            };
            if (!_.isUndefined(meta.rowactions.actions)) {
                rowActions.fields[0].buttons = meta.rowactions.actions;
            }
            if (!_.isUndefined(meta.rowactions.primary)) {
                rowActions.fields[0].primary = meta.rowactions.primary;
            }
            panel.fields = panel.fields.concat(rowActions);
        });

        return meta;
    },
    showTooltip: function(e) {
        this.$(e.currentTarget).tooltip("show");
    },
    hideTooltip: function(e) {
        this.$(e.currentTarget).tooltip("hide");
    },
    showActions:function (e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").show();
    },
    hideActions:function (e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").hide();
    },
    bindDataChange:function () {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})
